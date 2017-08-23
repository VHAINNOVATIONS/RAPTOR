using System;
using System.Collections.Generic;
using System.Collections;
using System.Text;
using System.Net;
using System.Net.Sockets;
using gov.va.medora.utils;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo.src.mdo;
using System.IO;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaConnection : AbstractConnection
    {
        public Dictionary<string, bool> IssedBulletin = new Dictionary<string, bool>();
        public IList<string> Rpcs = new List<string>();

        const int CONNECTION_TIMEOUT = 30000;
        const int READ_TIMEOUT = 120000;
        const int DEFAULT_PORT = 9200;

        public Socket socket;
        public int port;

        ISystemFileHandler sysFileHandler;

        public VistaConnection(DataSource dataSource) : base(dataSource) 
        {
            Account = new VistaAccount(this);
            if (ConnectTimeout == 0)
            {
                ConnectTimeout = CONNECTION_TIMEOUT;
            }
            if (ReadTimeout == 0)
            {
                ReadTimeout = READ_TIMEOUT;
            }
            port = (dataSource.Port == 0) ? DEFAULT_PORT : dataSource.Port;
            sysFileHandler = new VistaSystemFileHandler(this);
        }

        public override void connect()
        {
            IsConnecting = true;
            ConnectStrategy.connect();
            IsTestSource = isTestSystem();
            IsConnecting = false;
        }

        // Needs to return object so it can be either User or Exception on multi-site connections.
        public override object authorizedConnect(AbstractCredentials credentials, AbstractPermission permission, DataSource validationDataSource)
        {
            try
            {
                connect();
                return Account.authenticateAndAuthorize(credentials, permission, validationDataSource);
            }
            catch (Exception ex)
            {
                return ex;
            }
        }

        public override ISystemFileHandler SystemFileHandler
        {
            get 
            {
                if (sysFileHandler == null)
                {
                    sysFileHandler = new VistaSystemFileHandler(this);
                }
                return sysFileHandler; 
            }
        }

        public override object query(MdoQuery vq, AbstractPermission context = null) 
        {
            // see http://trac.medora.va.gov/web/ticket/2716 
            //if (Rpcs == null)
            //{
            //    Rpcs = new List<string>();
            //}
            //Rpcs.Add(vq.RpcName);

            //if (String.Equals(vq.RpcName, "DDR LISTER"))
            //{
            //    return query(vq, context, true);
            //}

            string request = vq.buildMessage();
            return query(request, context);
        }

        //public VistaRpcQuery query(MdoQuery request, AbstractPermission context, bool vistaRpcQuery)
        //{
        //    if (!vistaRpcQuery)
        //    {
        //        throw new NotImplementedException("Only used for DDR RPCs");
        //    }
        //    VistaRpcQuery result = new VistaRpcQuery();

        //    result.RequestString = request.buildMessage();
        //    result.RequestTime = DateTime.Now;
        //    result.ResponseString = (String)query(result.RequestString, context);
        //    result.ResponseTime = DateTime.Now;

        //    return result;
        //}

        public override object query(string request, AbstractPermission context = null)
        {
            // see http://trac.medora.va.gov/web/ticket/2716 
            //if (Rpcs == null)
            //{
            //    Rpcs = new List<string>();
            //}
            //try
            //{
            //    // TBD - do we want to just not log calls if not passed through query(MdoQuery)??? it seems excessive to use reflection on every query
            //    // to determine if the calling function was query(MdoQuery) and thus has already been logged.

            //    // don't want to duplicate calls being logged by query(MdoQuery) so make sure that was NOT the calling function
            //    if (!String.Equals(new System.Diagnostics.StackFrame(1).GetMethod().Name, "query", StringComparison.CurrentCultureIgnoreCase))
            //    {
            //        // we can't get RPC since we just received message but that information is human readable so save anyways
            //        Rpcs.Add(request);
            //    }
            //}
            //catch (Exception) { /* don't want to blow everything up - just hide this */ }

            if (!IsConnecting && !IsConnected)
            {
                throw new NotConnectedException();
            }
            AbstractPermission currentContext = null;
            if (context != null && context.Name != this.Account.PrimaryPermission.Name)
            {
                currentContext = this.Account.PrimaryPermission;
                ((VistaAccount)this.Account).setContext(context);
            }

            Byte[] bytesSent = Encoding.ASCII.GetBytes(request);
            Byte[] bytesReceived = new Byte[256];

            socket.Send(bytesSent, bytesSent.Length, 0);

            int bytes = 0;
            string reply = "";
            StringBuilder sb = new StringBuilder();
            string thisBatch = "";
            //bool isHdr = true;
            bool isErrorMsg = false;
            int endIdx = -1;

            // first read from socket so we don't need to use isHdr any more
            bytes = socket.Receive(bytesReceived, bytesReceived.Length, 0);
            if (bytes == 0)
            {
                throw new ConnectionException("Timeout waiting for response from VistA");
            }
            thisBatch = Encoding.ASCII.GetString(bytesReceived, 0, bytes);
            endIdx = thisBatch.IndexOf('\x04');
            if (endIdx != -1)
            {
                thisBatch = thisBatch.Substring(0, endIdx);
            }
            if (bytesReceived[0] != 0)
            {
                thisBatch = thisBatch.Substring(1, bytesReceived[0]);
                isErrorMsg = true;
            }
            else if (bytesReceived[1] != 0)
            {
                thisBatch = thisBatch.Substring(2);
                isErrorMsg = true;
            }
            else
            {
                thisBatch = thisBatch.Substring(2);
            }
            sb.Append(thisBatch);

            // now we can start reading from socket in a loop
            MemoryStream ms = new MemoryStream();
            while (endIdx == -1)
            {
                bytes = socket.Receive(bytesReceived, bytesReceived.Length, 0);
                if (bytes == 0)
                {
                    throw new ConnectionException("Timeout waiting for response from VistA");
                }
                for (int i = 0; i < bytes; i++)
                {
                    if (bytesReceived[i] == '\x04')
                    {
                        endIdx = i;
                        break;
                    }
                    else
                    {
                        ms.WriteByte(bytesReceived[i]);
                    }
                }
            }
            sb.Append(Encoding.ASCII.GetString(ms.ToArray()));

            reply = sb.ToString();

            if (currentContext != null)
            {
                ((VistaAccount)this.Account).setContext(currentContext);
            }

            if (isErrorMsg || reply.Contains("M  ERROR"))
            {
                throw new MdoException(MdoExceptionCode.VISTA_FAULT, reply);
            }

            //return StringUtils.stripInvalidXmlCharacters(reply); // start cleaning all Vista responses - too tedious to do on a case by case basis
            return reply;
        }

        public override void disconnect()
        {
            //if (!IsConnected)
            //{
            //    return;
            //}


            try
            {
                string msg = "[XWB]10304\x0005#BYE#\x0004";
                msg = (string)query(msg);
                socket.Disconnect(false);
                socket.Shutdown(SocketShutdown.Both);
                socket.Close();
                
                socket.Dispose();
               // System.Console.WriteLine("Successful disconnect!");
            }
            catch (Exception)
            {
              //  System.Console.WriteLine("Exception on disconnect: " + exc.Message);
            }
            finally
            {
                IsConnected = false; // must be down here because query depends on IsConnected = true
            }
        }

        public override string getWelcomeMessage()
        {
            MdoQuery request = buildGetWelcomeMessageRequest();
            string response = (string)query(request);
            return response;
        }

        internal MdoQuery buildGetWelcomeMessageRequest()
        {
           return new VistaQuery("XUS INTRO MSG");           
        }

        public override bool hasPatch(string patchId)
        {
            MdoQuery request = buildHasPatchRequest(patchId);
            string response = (string)query(request);
            return (response == "1");
        }

        internal MdoQuery buildHasPatchRequest(string patchId)
        {
            VistaQuery vq = new VistaQuery("ORWU PATCH");
            vq.addParameter(vq.LITERAL, patchId);
            return vq;
        }

        internal bool isTestSystem()
        {
            VistaQuery vq = new VistaQuery("XUS SIGNON SETUP");
            string response = (string)query(vq.buildMessage());
            string[] flds = StringUtils.split(response, StringUtils.CRLF);
            return flds[7] == "0";
        }

        public override string getServerTimeout()
        {
            string arg = "$P($G(^XTV(8989.3,1,\"XWB\")),U)";
            MdoQuery request = VistaUtils.buildGetVariableValueRequest(arg);
            string response = (string)query(request);
            return response;
        }

        public override object query(SqlQuery request, Delegate functionToInvoke, AbstractPermission permission = null)
        {
            throw new NotImplementedException();
        }

        #region Symbol Table
        /// <summary>
        /// Set the connections session variable to the symbol table
        /// </summary>
        public void setSymbolTable()
        {
            setState(this.Session);
        }

        public override void setState(Dictionary<string, object> sessionTable)
        {
            MdoQuery request = buildSetSymbolTableRequest(sessionTable);
            string response = (string)query(request.buildMessage());
            if (!String.Equals(response, "1"))
            {
                throw new MdoException("Unable to deserialize symbol table! Vista code: {0}", response);
            }
        }

        internal MdoQuery buildSetSymbolTableRequest(Dictionary<string, object> sessionTable)
        {
            VistaQuery request = new VistaQuery("XWB DESERIALIZE");
            DictionaryHashList dhl = new DictionaryHashList();
            string[] allKeys = new string[sessionTable.Count];
            sessionTable.Keys.CopyTo(allKeys, 0);

            for (int i = 0; i < sessionTable.Count; i++)
            {
                dhl.Add((i + 1).ToString(), (object)String.Concat(allKeys[i], '\x1e', sessionTable[allKeys[i]]));
            }

            request.addParameter(request.LIST, dhl);
            return request;
        }

        public override Dictionary<string, object> getState()
        {
            return getSerializedSymbolTable();
        }

        internal Dictionary<string, object> getSerializedSymbolTable()
        {
            MdoQuery request = buildGetSerializedSymbolTableRequest();
            string response = (string)query(request);
            return toSerializedSymbolTable(response);
        }

        internal MdoQuery buildGetSerializedSymbolTableRequest()
        {
            MdoQuery request = new VistaQuery("XWB SERIALIZE");
            return request;
        }

        internal Dictionary<string, object> toSerializedSymbolTable(string response)
        {
            Dictionary<string, object> result = new Dictionary<string, object>();

            string[] lines = StringUtils.split(response, '\x1f');
            foreach (string line in lines)
            {
                if (String.IsNullOrEmpty(line))
                {
                    continue;
                }
                string[] pieces = StringUtils.split(line, '\x1e');
                string key = pieces[0];
                object value = null;

                if (String.IsNullOrEmpty(pieces[1]) || pieces[1].StartsWith("\""))
                {
                    value = pieces[1] as string;
                }
                else
                {
                    value = pieces[1];
                }

                result.Add(key, value);
            }
            return result;
        }
        #endregion

        public void heartbeat()
        {
            VistaQuery vq = new VistaQuery("XWB IM HERE");
            this.query(vq);
        }

    }
}

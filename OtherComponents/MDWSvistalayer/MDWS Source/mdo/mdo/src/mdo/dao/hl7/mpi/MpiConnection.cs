using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using System.Net;
using System.Net.Sockets;
using gov.va.medora.utils;
using gov.va.medora.mdo.src.mdo;

namespace gov.va.medora.mdo.dao.hl7.mpi
{
    public class MpiConnection : AbstractConnection
    {
        const int DEFAULT_PORT = 15500;
        const int DEFAULT_TIMEOUT = 60000;
        const string END_MESSAGE = "\u001B\u001B\u001B";

        string hostname;
        int port;
        int timeout;
        Socket socket;

        public MpiConnection(DataSource dataSource) : base(dataSource) { }

        public override void connect()
        {
            IsConnected = false;

            if (DataSource == null)
            {
                throw new Exception("No data source");
            }
            if (DataSource.Protocol != "HL7")
            {
                throw new Exception("Incorrect protocol: " + DataSource.Protocol + ". Should be HL7.");
            }
            if (DataSource.Modality != "MPI")
            {
                throw new Exception("Incorrect modality: " + DataSource.Modality + ". Should be MPI.");
            }

            hostname = DataSource.Provider;
            if (StringUtils.isEmpty(hostname))
            {
                throw new Exception("No provider (hostname)");
            }
            port = DataSource.Port;
            if (port == 0)
            {
                port = DEFAULT_PORT;
            }
            timeout = DEFAULT_TIMEOUT;

            try
            {
                IPAddress mpiIP = (IPAddress)Dns.GetHostEntry(hostname).AddressList[0];
                IPEndPoint mpiEndPoint = new IPEndPoint(mpiIP, port);
                socket = new Socket(AddressFamily.InterNetwork, SocketType.Stream, ProtocolType.Tcp);
                socket.SetSocketOption(SocketOptionLevel.Socket, SocketOptionName.ReceiveTimeout, timeout);
                socket.Connect(mpiEndPoint);
                if (!socket.Connected)
                {
                    throw new Exception("Unable to connect to " + hostname + ", port " + port);
                }
            }
            catch (SocketException se)
            {
                Exception nse = new Exception(se.Message + ". Tried " + hostname + ", port " + port, se);
                throw nse;
            }

            IsConnected = true;
        }

        public override void disconnect()
        {
            if (!IsConnected)
            {
                return;
            }
            socket.Close();
            IsConnected = false;
        }

        public override object query(MdoQuery request, AbstractPermission permission = null)
        {
            throw new NotImplementedException();
        }

        public override object query(string request, AbstractPermission permission = null)
        {
            connect();
            string msg = "HELO " + DataSource.SiteId.Id + "\r\n";
            string reply = sendReceive(msg, "\r\n");
            if (!reply.StartsWith("220"))
            {
                disconnect();
                throw new Exception("ERROR sending HELO: " + reply);
            }
            string datamsg = "DATA PARAM=MPI\r\n";
            string hl7msg = "";
            string[] segments = StringUtils.split(request, "\r");
            segments = StringUtils.trimArray(segments);
            for (int i = 0; i < segments.Length; i++)
            {
                segments[i] += '\r';    //Gotta put the terminator back after splitting on it
                hl7msg += StringUtils.strPack(segments[i], 3);
            }
            hl7msg += StringUtils.strPack(END_MESSAGE, 3);

            send(datamsg);
            reply = sendReceive(hl7msg, "\r\n");
            if (!reply.StartsWith("220"))
            {
                disconnect();
                throw new Exception("ERROR sending DATA PARAM=MPI: " + reply);
            }
            msg = "TURN\r\n";
            reply = sendReceive(msg, "\r\n");
            if (!reply.StartsWith("220"))
            {
                disconnect();
                throw new Exception("ERROR sending HL7: " + reply);
            }
            reply = receive(END_MESSAGE);
            msg = "QUIT\r\n";
            send(msg);
            disconnect();
            return reply;
        }

        internal string sendReceive(string request, string terminateString)
        {
            send(request);
            return receive(terminateString);
        }

        internal void send(string msg)
        {
            Byte[] bytesSent = Encoding.ASCII.GetBytes(msg);
            socket.Send(bytesSent, bytesSent.Length, 0);
        }

        internal string receive(string terminateString)
        {
            Byte[] bytesReceived = new Byte[256];
            int bytes = 0;
            string reply = "";
            int endIdx = -1;
            do
            {
                bytes = socket.Receive(bytesReceived, bytesReceived.Length, 0);
                string thisBatch = Encoding.ASCII.GetString(bytesReceived, 0, bytes);
                endIdx = thisBatch.IndexOf(terminateString);
                if (endIdx != -1)
                {
                    thisBatch = thisBatch.Substring(0, endIdx);
                }
                reply += thisBatch;
            }
            while (endIdx == -1);

            return reply;
        }

        public override string getWelcomeMessage()
        {
            return null;
        }

        public override ISystemFileHandler SystemFileHandler
        {
            get { return null; }
        }

        //public override object authorizedConnect(AbstractCredentials credentials, AbstractPermission permission)
        //{
        //    return null;
        //}

        public override object authorizedConnect(AbstractCredentials credentials, AbstractPermission permission, DataSource validationDataSource = null)
        {
            return null;
        }

        public override bool hasPatch(string patchId)
        {
            throw new Exception("The method or operation is not implemented.");
        }

        public override string getServerTimeout()
        {
            return null;
        }


        public override object query(SqlQuery request, Delegate functionToInvoke, AbstractPermission permission = null)
        {
            throw new NotImplementedException();
        }

        public override Dictionary<string, object> getState()
        {
            throw new NotImplementedException();
        }

        public override void setState(Dictionary<string, object> session)
        {
            throw new NotImplementedException();
        }

        public override bool isAlive()
        {
            throw new NotImplementedException();
        }
    }
}

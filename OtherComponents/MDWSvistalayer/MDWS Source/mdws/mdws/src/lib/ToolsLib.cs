using System;
using System.Web;
using System.Collections.Specialized;
using gov.va.medora.mdo;
using gov.va.medora.utils;
using gov.va.medora.mdo.api;
using gov.va.medora.mdws.dto;
using gov.va.medora.mdo.dao;
using System.Net.Mail;
using System.Net;
using gov.va.medora.mdws.dto.vista.mgt;
using System.Collections.Generic;
using gov.va.medora.mdo.dao.vista;
//using System.Runtime.Serialization.Json;
using gov.va.medora.mdo.domain.pool.connection;

namespace gov.va.medora.mdws
{
    public class ToolsLib
    {
        MySession mySession;

        public ToolsLib(MySession mySession)
        {
            this.mySession = mySession;
        }

        public TextTO isRpcAvailable(string target, string context)
        {
            return isRpcAvailable(null,target,context);
        }

        public TextTO isRpcAvailable(string sitecode, string target, string context)
        {
            TextTO result = new TextTO();
            string msg = MdwsUtils.isAuthorizedConnection(mySession, sitecode);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            else if (target == "")
            {
                result.fault = new FaultTO("Missing target");
            }
            else if (context == "")
            {
                result.fault = new FaultTO("Missing context");
            }
            if (result.fault != null)
            {
                return result;
            }

            if (sitecode == null)
            {
                sitecode = mySession.ConnectionSet.BaseSiteId;
            }

            try
            {
                AbstractConnection cxn = mySession.ConnectionSet.getConnection(sitecode);
                ToolsApi api = new ToolsApi();
                string s = api.isRpcAvailable(cxn, target, context);
                result = new TextTO(s);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TextArray ddrLister(
            string file,
            string iens,
            string fields,
            string flags,
            string maxrex,
            string from,
            string part,
            string xref,
            string screen,
            string identifier)
        {
            return ddrLister(null,file,iens,fields,flags,maxrex,from,part,xref,screen,identifier);
        }

        public TextArray ddrListerPlus(
            string sitecode,
            string file,
            string iens,
            string fields,
            string flags,
            string maxrex,
            string from,
            string part,
            string xref,
            string screen,
            string identifier)
        {
            AbstractConnection cxn = null;
            try
            {
                cxn = (AbstractConnection)ConnectionPools.getInstance().checkOutAlive(sitecode);
                DdrLister ddr = new DdrLister(cxn)
                {
                    File = file,
                    Iens = iens,
                    Fields = fields,
                    Flags = flags,
                    Max = maxrex,
                    From = from,
                    Part = part,
                    Xref = String.IsNullOrEmpty(xref) ? "#" : xref,
                    Screen = screen,
                    Id = identifier
                };
                return new TextArray(ddr.execute());
                //VistaRpcQuery ddrResult = ddr.execute();
                //return new DdrRpcResult(ddrResult);
            }
            catch (System.Net.Sockets.SocketException se)
            {
                try
                {
                    cxn.disconnect();
                }
                catch (Exception) { }
                return new TextArray() { fault = new FaultTO(se) };
                //return new DdrRpcResult() { fault = new FaultTO(se) };
            }
            catch (Exception exc)
            {
                return new TextArray() { fault = new FaultTO(exc) };
                //return new DdrRpcResult() { fault = new FaultTO(exc) };
            }
            finally
            {
                if (cxn != null)
                {
                    ConnectionPools.getInstance().checkIn(cxn);
                }
            }
        }

        public TextArray ddrLister(
            string sitecode,
            string file,
            string iens,
            string fields,
            string flags,
            string maxrex,
            string from,
            string part,
            string xref,
            string screen,
            string identifier)
        {
            TextArray result = new TextArray();
            string msg = MdwsUtils.isAuthorizedConnection(mySession, sitecode);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            if (result.fault != null)
            {
                return result;
            }

            if (sitecode == null)
            {
                sitecode = mySession.ConnectionSet.BaseSiteId;
            }

            try
            {
                AbstractConnection cxn = mySession.ConnectionSet.getConnection(sitecode);
                ToolsApi api = new ToolsApi();
                string[] s = api.ddrLister(cxn, file, iens, fields, flags, maxrex, from, part, xref, screen, identifier);
                result = new TextArray(s);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TaggedTextArray ddrListerMS(
            string file,
            string iens,
            string fields,
            string flags,
            string maxrex,
            string from,
            string part,
            string xref,
            string screen,
            string identifier)
        {
            TaggedTextArray result = new TaggedTextArray();
            try
            {
                ToolsApi api = new ToolsApi();
                IndexedHashtable s = api.ddrLister(mySession.ConnectionSet, file, iens, fields, flags, maxrex, from, part, xref, screen, identifier);
                result = new TaggedTextArray(s);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TextTO getVariableValue(string arg)
        {
            return getVariableValue(null,arg);
        }

        public TextTO getVariableValue(string sitecode, string arg)
        {
            TextTO result = new TextTO();
            string msg = MdwsUtils.isAuthorizedConnection(mySession, sitecode);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            if (result.fault != null)
            {
                return result;
            }

            if (sitecode == null)
            {
                sitecode = mySession.ConnectionSet.BaseSiteId;
            }

            try
            {
                AbstractConnection cxn = mySession.ConnectionSet.getConnection(sitecode);
                ToolsApi api = new ToolsApi();
                string s = api.getVariableValue(cxn, arg);
                result = new TextTO(s);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TextTO sendEmail(string from, string to, string subject, string body, string isBodyHTML, string username, string password)
        {
            TextTO result = new TextTO();
            if (String.IsNullOrEmpty(from) || String.IsNullOrEmpty(to) ||
                String.IsNullOrEmpty(subject) || String.IsNullOrEmpty(body))
            {
                result.fault = new FaultTO("Must supply all parameters", "Supply a value for each of the arguments");
            }
            if (result.fault != null)
            {
                return result;
            }

            string host = "smtp.va.gov";
            int port = 25;
            bool enableSsl = false;
            bool useDefaultCredentials = false;

            try
            {
                MailMessage message = new MailMessage();
                message.From = new MailAddress(from);
                if (!String.IsNullOrEmpty(isBodyHTML))
                {
                    message.IsBodyHtml = isBodyHTML.ToUpper().Equals("TRUE") ? true : false;
                }
                message.Body = body;
                message.Subject = subject;
                //to contains comma seperated email addresses
                message.To.Add(to);

                SmtpClient smtpClient = new SmtpClient(host, port);
                smtpClient.EnableSsl = enableSsl;
                smtpClient.UseDefaultCredentials = useDefaultCredentials;

                if (!useDefaultCredentials && !String.IsNullOrEmpty(username) && !String.IsNullOrEmpty(password))
                {
                    smtpClient.Credentials = new NetworkCredential(username, password);
                }

                smtpClient.Send(message);
                result.text = "OK";
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc.Message);
            }
            return result;
        }

        public TextArray ddrGetsEntry(string file, string iens, string flds, string flags)
        {
            TextArray result = new TextArray();
            string msg = MdwsUtils.isAuthorizedConnection(mySession);
            if (msg != "OK")
            {
                result.fault = new FaultTO(msg);
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                ToolsApi api = new ToolsApi();
                string[] response = api.ddrGetsEntry(mySession.ConnectionSet.BaseConnection, file, iens, flds, flags);
                return new TextArray(response);
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
                return result;
            }
        }

        public TaggedTextArray runRpc(string rpcName, string[] paramValues, int[] paramTypes, bool[] paramEncrypted)
        {
            TaggedTextArray result = new TaggedTextArray();

            try
            {
                ToolsApi api = new ToolsApi();
                IndexedHashtable s = api.runRpc(mySession.ConnectionSet, rpcName, paramValues, paramTypes, paramEncrypted);
                return new TaggedTextArray(s);
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
                return result;
            }
        }

        public VistaFileTO getFile(string fileNumber, bool includeXRefs)
        {
            VistaFileTO result = new VistaFileTO();

            try
            {
                IndexedHashtable ihs = new ToolsApi().getFile(mySession.ConnectionSet, fileNumber, includeXRefs);
                result = new VistaFileTO(ihs.GetValue(0) as gov.va.medora.mdo.dao.vista.VistaFile);
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

        public XRefArray getXRefs(string fileNumber)
        {
            XRefArray result = new XRefArray();

            try
            {
                IndexedHashtable ihs = new ToolsApi().getXRefs(mySession.ConnectionSet, fileNumber);
                Dictionary<string, CrossRef> xrefs = (Dictionary<string, CrossRef>)ihs.GetValue(0);
                result = new XRefArray(xrefs);
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

        public TextTO create(String jsonDictionaryFieldsAndValues, String file, String iens)
        {
            try
            {
                Dictionary<String, String> deserializedDict = JsonUtils.Deserialize<Dictionary<String, String>>(jsonDictionaryFieldsAndValues);
                VistaFieldTO[] fieldArray = new VistaFieldTO[deserializedDict.Count];
                int counter = 0;
                foreach (String key in deserializedDict.Keys)
                {
                    VistaFieldTO current = new VistaFieldTO() { number = key, value = deserializedDict[key] };
                    fieldArray[counter] = current;
                }
                VistaRecordTO record = new VistaRecordTO() { fields = fieldArray, file = new VistaFileTO() { number = file }, iens = iens };
                return create(record);
            }
            catch (Exception exc)
            {
                return new TextTO() { fault = new FaultTO(exc) };
            }
        }

        public TextTO create(VistaRecordTO record)
        {
            TextTO result = new TextTO();

            if (record == null || record.fields == null || record.fields.Length < 1)
            {
                result.fault = new FaultTO("The record must have at least 1 field");
            }
            if (record.file == null || String.IsNullOrEmpty(record.file.number))
            {
                result.fault = new FaultTO("You must specify the file number in which this record should be created");
            }
            foreach (VistaFieldTO field in record.fields)
            {
                if (field == null || String.IsNullOrEmpty(field.number) || String.IsNullOrEmpty(field.value))
                {
                    result.fault = new FaultTO("All fields must have a number and value");
                    break;
                }
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                Dictionary<String, String> keysAndValues = new Dictionary<string, string>();
                foreach (VistaFieldTO field in record.fields)
                {
                    keysAndValues.Add(field.number, field.value);
                }
                CrudOperation co = new ToolsApi().create(mySession.ConnectionSet.BaseConnection, keysAndValues, record.file.number, record.iens);
                result.text = (String)co.Result;
                //result.rpc = new RpcTO(co.RPC);
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

        public VistaRecordTO read(String recordId, String fields, String file)
        {
            VistaRecordTO result = new VistaRecordTO();

            if (String.IsNullOrEmpty(fields))
            {
                result.fault = new FaultTO("Missing fields");
            }
            else if (String.IsNullOrEmpty(file))
            {
                result.fault = new FaultTO("Missing file");
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                CrudOperation co = new ToolsApi().read(mySession.ConnectionSet.BaseConnection, recordId, fields, file);
                Dictionary<String, String> fieldsAndValues = (Dictionary<String, String>)co.Result;
                //result.rpc = new RpcTO(co.RPC);
                result.file = new VistaFileTO() { number = file };
                result.ien = recordId;
                result.siteId = mySession.ConnectionSet.BaseConnection.DataSource.SiteId.Id;
                result.fields = new VistaFieldTO[fieldsAndValues.Count];
                String[] allKeys = new String[fieldsAndValues.Count];
                fieldsAndValues.Keys.CopyTo(allKeys, 0);
                for (int i = 0; i < allKeys.Length; i++)
                {
                    result.fields[i] = new VistaFieldTO() { number = allKeys[i], value = fieldsAndValues[allKeys[i]] };
                }
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

        public TextArray readRange(String file, String fields, String iens, String flags, String xref, String maxRex, String from, String part, String screen, String identifier)
        {
            TextArray result = new TextArray();

            if (String.IsNullOrEmpty(fields))
            {
                result.fault = new FaultTO("Missing fields");
            }
            else if (String.IsNullOrEmpty(file))
            {
                result.fault = new FaultTO("Missing file");
            }

            if (result.fault != null)
            {
                return result;
            }

            // can't pass '#' character in a URL so must leave blank and therefore default to this xref
            if (String.IsNullOrEmpty(xref))
            {
                xref = "#";
            }

            try
            {
                CrudOperation co = new ToolsApi().readRange(mySession.ConnectionSet.BaseConnection, file, fields, iens, flags, xref, maxRex, from, part, screen, identifier);
                String[] ddrListerResults = (String[])co.Result;
                //result.rpc = new RpcTO(co.RPC);
                result.text = ddrListerResults;
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }

            return result;
        }

        public TextTO update(String jsonDictionaryFieldsAndValues, String recordId, String file)
        {
            try
            {
                Dictionary<String, String> deserializedDict = JsonUtils.Deserialize<Dictionary<String, String>>(jsonDictionaryFieldsAndValues);
                VistaFieldTO[] fieldArray = new VistaFieldTO[deserializedDict.Count];
                int counter = 0;
                foreach (String key in deserializedDict.Keys)
                {
                    VistaFieldTO current = new VistaFieldTO() { number = key, value = deserializedDict[key] };
                    fieldArray[counter] = current;
                }
                VistaRecordTO record = new VistaRecordTO() { fields = fieldArray, file = new VistaFileTO() { number = file }, iens = recordId };
                return create(record);
            }
            catch (Exception exc)
            {
                return new TextTO() { fault = new FaultTO(exc) };
            }
        }

        public TextTO update(VistaRecordTO record)
        {
            TextTO result = new TextTO();

            if (record == null || record.fields == null || record.fields.Length < 1)
            {
                result.fault = new FaultTO("The record must have at least 1 field");
            }
            if (record.file == null || String.IsNullOrEmpty(record.file.number))
            {
                result.fault = new FaultTO("You must specify the file number in which this record should be created");
            }
            foreach (VistaFieldTO field in record.fields)
            {
                if (field == null || String.IsNullOrEmpty(field.number) || String.IsNullOrEmpty(field.value))
                {
                    result.fault = new FaultTO("All fields must have a number and value");
                    break;
                }
            }
            if (String.IsNullOrEmpty(record.iens))
            {
                result.fault = new FaultTO("You must supply the IENS string for the record being updated");
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                Dictionary<String, String> keysAndValues = new Dictionary<string, string>();
                foreach (VistaFieldTO field in record.fields)
                {
                    keysAndValues.Add(field.number, field.value);
                }
                CrudOperation co = new ToolsApi().update(mySession.ConnectionSet.BaseConnection, keysAndValues, record.iens, record.file.number);
                //result.rpc = new RpcTO(co.RPC);
                result.text = "OK";
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

        public TextTO delete(String recordId, String file)
        {
            TextTO result = new TextTO();

            if (String.IsNullOrEmpty(recordId))
            {
                result.fault = new FaultTO("Missing record ID");
            }
            else if (String.IsNullOrEmpty(file))
            {
                result.fault = new FaultTO("Missing file");
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                CrudOperation co = new ToolsApi().delete(mySession.ConnectionSet.BaseConnection, recordId, file);
                //result.rpc = new RpcTO(co.RPC);
                result.text = "OK";
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }
    }
}

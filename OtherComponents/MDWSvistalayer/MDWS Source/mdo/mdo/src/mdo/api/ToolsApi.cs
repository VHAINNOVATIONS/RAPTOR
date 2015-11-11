using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.dao.vista;

namespace gov.va.medora.mdo.api
{
    public class ToolsApi
    {
        public ToolsApi() { }

        public string[] ddrLister(
            AbstractConnection cxn,
            string file,
            string iens,
            string flds,
            string flags,
            string maxRex,
            string from,
            string part,
            string xref,
            string screen,
            string identifier)
        {
            VistaToolsDao dao = new VistaToolsDao(cxn);
            return dao.ddrLister(file, iens, flds, flags, maxRex, from, part, xref, screen, identifier);
        }

        public IndexedHashtable ddrLister(
            ConnectionSet cxns,
            string file,
            string iens,
            string flds,
            string flags,
            string maxRex,
            string from,
            string part,
            string xref,
            string screen,
            string identifier)
        {
            object[] args = new object[]
            {
                file,iens,flds,flags,maxRex,from,part,xref,screen,identifier
            };
            return cxns.query("IToolsDao", "ddrLister", args);
        }

        public string getVariableValue(AbstractConnection cxn, string arg)
        {
            VistaToolsDao dao = new VistaToolsDao(cxn);
            return dao.getVariableValue(arg);
        }

        public IndexedHashtable getVariableValue(ConnectionSet cxns, string arg)
        {
            return cxns.query("IToolsDao", "getVariableValue", new object[] { arg });
        }

        public IndexedHashtable getRpcList(ConnectionSet cxns, string target)
        {
            return cxns.query("IToolsDao", "getRpcList", new object[] { target });
        }

        public KeyValuePair<string, string>[] getRpcList(AbstractConnection cxn, string target)
        {
            VistaToolsDao dao = new VistaToolsDao(cxn);
            return dao.getRpcList(target);
        }

        public string getRpcName(AbstractConnection cxn, string rpcIEN)
        {
            VistaToolsDao dao = new VistaToolsDao(cxn);
            return dao.getRpcName(rpcIEN);
        }

        public IndexedHashtable isRpcAvailableAtSite(ConnectionSet cxns, string target, string localRemote, string version)
        {
            return cxns.query("IToolsDao", "isRpcAvailableAtSite", new object[] { target, localRemote, version });
        }

        public bool isRpcAvailableAtSite(AbstractConnection cxn, string target, string localRemote, string version)
        {
            VistaToolsDao dao = new VistaToolsDao(cxn);
            return dao.isRpcAvailableAtSite(target, localRemote, version);
        }

        public IndexedHashtable isRpcAvailable(ConnectionSet cxns, string target, string context)
        {
            return cxns.query("IToolsDao", "isRpcAvailable", new object[] { target, context });
        }

        public string isRpcAvailable(AbstractConnection cxn, string target, string context)
        {
            VistaToolsDao dao = new VistaToolsDao(cxn);
            return dao.isRpcAvailable(target, context);
        }

        public IndexedHashtable isRpcAvailable(ConnectionSet cxns, string target, string context, string localRemote, string version)
        {
            return cxns.query("IToolsDao", "isRpcAvailable", new object[] { target, context, localRemote, version });
        }

        public string isRpcAvailable(AbstractConnection cxn, string target, string context, string localRemote, string version)
        {
            VistaToolsDao dao = new VistaToolsDao(cxn);
            return dao.isRpcAvailable(target, context, localRemote, version);
        }

        public string[] ddrGetsEntry(AbstractConnection cxn, string file, string iens, string flds, string flags)
        {
            VistaToolsDao dao = new VistaToolsDao(cxn);
            return dao.ddrGetsEntry(file, iens, flds, flags);
        }

        public IndexedHashtable hasPatch(ConnectionSet cxns, string patchId)
        {
            return cxns.query("AbstractConnection", "hasPatch", new object[] { patchId });
        }

        //public bool hasPatch(AbstractConnection cxn, string patchId)
        //{

        //}

        public string runRpc(AbstractConnection cxn, string rpcName, string[] paramValues, int[] paramTypes, bool[] paramEncrypted)
        {
            VistaToolsDao dao = new VistaToolsDao(cxn);
            return dao.runRpc(rpcName, paramValues, paramTypes, paramEncrypted);
        }

        public IndexedHashtable runRpc(ConnectionSet cxns, string rpcName, string[] paramValues, int[] paramTypes, bool[] paramEncrypted)
        {
            return cxns.query("IToolsDao", "runRpc", new object[] { rpcName, paramValues, paramTypes, paramEncrypted });
        }

        public IndexedHashtable getFile(ConnectionSet cxns, string fileNumber, bool includeXRefs)
        {
            return cxns.query("IToolsDao", "getFile", new object[] { fileNumber, includeXRefs });
        }

        public IndexedHashtable getXRefs(ConnectionSet cxns, string fileNumber)
        {
            return cxns.query("IToolsDao", "getXRefs", new object[] { new VistaFile() { FileNumber = fileNumber } });
        }

        public CrudOperation create(AbstractConnection cxn, Dictionary<String, String> fieldsAndValues, String file, String parentRecordId = null)
        {
            return new VistaCrudDao(cxn).create(fieldsAndValues, file, parentRecordId);
        }

        public CrudOperation read(AbstractConnection cxn, String recordId, String recordFields, String file)
        {
            return new VistaCrudDao(cxn).read(recordId, recordFields, file);
        }

        public CrudOperation readRange(AbstractConnection cxn, String file, String fields, String iens, String flags, String xref, String maxRex, String from, String part, String screen, String identifier)
        {
            return new VistaCrudDao(cxn).readRange(file, fields, iens, flags, xref, maxRex, from, part, screen, identifier);
        }

        public CrudOperation update(AbstractConnection cxn, Dictionary<String, String> fieldsAndValues, String recordId, String file)
        {
            return new VistaCrudDao(cxn).update(fieldsAndValues, recordId, file);
        }

        public CrudOperation delete(AbstractConnection cxn, String recordId, String file)
        {
            return new VistaCrudDao(cxn).delete(recordId, file);
        }
    }
}

using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao
{
    public interface IToolsDao
    {
        string[] ddrLister(
            string file,
            string iens,
            string flds,
            string flags,
            string maxRex,
            string from,
            string part,
            string xref,
            string screen,
            string identifier
            );
        string getVariableValue(string arg);
        KeyValuePair<string, string>[] getRpcList(string target);
        string getRpcName(string rpcIEN);
        bool isRpcAvailableAtSite(string target, string localRemote, string version);
        string isRpcAvailable(string target, string context);
        string isRpcAvailable(string target, string context, string localRemote, string version);
        string getTimestamp();
        string runRpc(string rpcName, string[] paramValues, int[] paramTypes, bool[] paramEncrypted);
        vista.VistaFile getFile(string fileNumber, bool includeXRefs);
        Dictionary<string, vista.CrossRef> getXRefs(vista.VistaFile file);
    }
}

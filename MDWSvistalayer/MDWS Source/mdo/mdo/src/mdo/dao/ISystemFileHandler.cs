using System;
using System.Collections;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;

namespace gov.va.medora.mdo.dao
{
    public interface ISystemFileHandler
    {
        Hashtable LookupTables();
        Hashtable Files();
        Dictionary<string, object> getFile(string file);
        StringDictionary getLookupTable(string file);
    }
}

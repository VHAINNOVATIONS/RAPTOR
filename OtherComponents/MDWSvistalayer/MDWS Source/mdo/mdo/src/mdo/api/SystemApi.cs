using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo.dao;

namespace gov.va.medora.mdo.api
{
    public class SystemApi
    {
        string daoName = "SystemDao";

        public SystemApi() { }

        public DateTime getTimestamp(Connection cxn)
        {
            return ((SystemDao)cxn.getDao(daoName)).getTimestamp();
        }

        public IndexedHashtable getTimestamp(MultiSourceQuery msq)
        {
            return msq.execute(daoName, "getTimestamp", new object[] { });
        }

    }
}

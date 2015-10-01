using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao.oracle.mhv
{
    public class MhvConstants
    {
        public const string DEFAULT_CXN_STRING = "Data Source=" +
            "(DESCRIPTION=" +
                "(ADDRESS=" +
                    "(PROTOCOL=TCP)" +
                    "(HOST=edbdbs4.aac.va.gov)" +
                    "(PORT=1591)" +
                ")" +
                "(CONNECT_DATA=" +
                    "(SERVICE_NAME=ADRRP.aac.va.gov)" +
                ")" +
            ");" +
            "User ID=vhaanngilloj;" +
            "Password=gumshoe5_;";
    }
}

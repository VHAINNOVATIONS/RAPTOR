using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
//using System.Data.OracleClient;
using Oracle.DataAccess.Client;

namespace gov.va.medora.mdo
{
    public class OracleQuery : SqlQuery
    {
        public OracleCommand Command { get; set; }
    }
}

using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Data.SqlClient;

namespace gov.va.medora.mdo
{
    public abstract class SqlQuery
    {
        public List<SqlParameter> Parameters { get; set; }
        public string CommandText { get; set; }
    }
}

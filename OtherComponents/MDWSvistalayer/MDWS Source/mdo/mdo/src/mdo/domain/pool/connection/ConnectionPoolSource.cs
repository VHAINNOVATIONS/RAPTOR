using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.pool.connection
{
    public class ConnectionPoolSource : AbstractPoolSource
    {
        public DataSource CxnSource { get; set; }
    }
}

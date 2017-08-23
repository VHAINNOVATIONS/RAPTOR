using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading;
using gov.va.medora.mdo.dao;

namespace gov.va.medora.mdo.domain.pool
{
    public class AbstractResourceThread
    {
        public Thread Thread { get; set; }
       // public System.Threading.Tasks.Task Thread { get; set; }

        public DateTime Timestamp { get; set; }

        public AbstractPoolSource Source { get; set; }
    }
}

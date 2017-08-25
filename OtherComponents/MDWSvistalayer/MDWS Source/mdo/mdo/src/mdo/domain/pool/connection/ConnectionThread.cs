using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.dao;

namespace gov.va.medora.mdo.domain.pool.connection
{
    public class ConnectionThread : AbstractResourceThread
    {
        public ConnectionThread() 
        { 
            this.Timestamp = DateTime.Now; 
        }
        
        public AbstractConnection Connection { get; set; }
    }
}

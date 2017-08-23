using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.dao
{
    public class QueryThreadConnectionPool
    {
        Dictionary<string, IList<AbstractConnection>> _cxns;
        public EventHandler Changed;

        public virtual void OnChanged(EventArgs e)
        {
            if (Changed != null)
            {
                Changed(this, e);
            }
        }

        public void addConnection(string sitecode, AbstractConnection cxn)
        {
            lock (_cxns)
            {
                if (!_cxns.ContainsKey(sitecode))
                {
                    _cxns.Add(sitecode, new List<AbstractConnection>());
                }
                _cxns[sitecode].Add(cxn);
            }
        }
    }
}

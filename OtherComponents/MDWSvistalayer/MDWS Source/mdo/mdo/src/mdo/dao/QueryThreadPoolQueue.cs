using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.dao
{
    public class QueryThreadPoolQueue
    {
        Dictionary<string, Queue<QueryThread>> _connectionQueues = new Dictionary<string, Queue<QueryThread>>();
        
        public event EventHandler Changed;

        public virtual void QueueChanged(EventArgs e)
        {
            if (Changed != null)
            {
                Changed(this, e);
            }
        }

        public QueryThread dequeue(string sitecode)
        {
            if (_connectionQueues.ContainsKey(sitecode))
            {
                lock (_connectionQueues)
                {
                    if (_connectionQueues[sitecode] != null && 
                        _connectionQueues[sitecode].Count > 0 &&
                        _connectionQueues[sitecode].Peek() != null)
                    {
                        return _connectionQueues[sitecode].Dequeue();
                    }
                }
            }
            return null;
        }

        public void queue(QueryThread qt, string siteId)
        {
            lock (_connectionQueues)
            {
                if (!_connectionQueues.ContainsKey(siteId))
                {
                    _connectionQueues.Add(siteId, new Queue<QueryThread>());
                }
                _connectionQueues[siteId].Enqueue(qt);
            }
            QueryThreadPoolEventArgs e = new QueryThreadPoolEventArgs()
            {
                QueueEventType = QueryThreadPoolEventArgs.QueueChangeEventType.QueryAdded,
                SiteId = siteId
            };
            QueueChanged(e);
        }
    }
}

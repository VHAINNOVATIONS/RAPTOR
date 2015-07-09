using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.dao
{
    public class QueryThreadPoolEventArgs : EventArgs
    {
        public enum QueueChangeEventType
        {
            QueryAdded,
            QueryRemoved,
            QueueEmpty
        }

        public enum ConnectionChangeEventType
        {
            ConnectionAvailable,
            ConnectionBusy,
            Disconnected
        }

        public ConnectionChangeEventType ConnectionEventType { get; set; }
        public QueueChangeEventType QueueEventType { get; set; }
        public string SiteId { get; set; }
    }
}

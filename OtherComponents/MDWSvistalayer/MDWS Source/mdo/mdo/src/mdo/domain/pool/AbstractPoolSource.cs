using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.dao;

namespace gov.va.medora.mdo.domain.pool
{
    public abstract class AbstractPoolSource
    {
        /// <summary>
        /// Use this property to specify the max number of times a resource should be used before it's "recycled". This is useful in
        /// the case of Vista connections, for example, to help improve performance as time goes on and the number of requests on a 
        /// persisted connection in the pool increases
        /// </summary>
        public int RecycleCount { get; set; }
        public int MaxPoolSize { get; set; }
        public int MinPoolSize { get; set; }
        public int PoolExpansionSize { get; set; }
        public AbstractCredentials Credentials { get; set; }
        public AbstractPermission Permission { get; set; }
        public LoadingStrategy LoadStrategy { get; set; }

        public Int32 MaxConsecutiveErrors { get; set; }
        public TimeSpan WaitOnMaxConsecutiveErrors { get; set; }

        TimeSpan _waitTime = new TimeSpan(0, 0, 30); // default
        /// <summary>
        /// Set the time to block waiting for a connection from the pool
        /// </summary>
        public TimeSpan WaitTime { get { return _waitTime; } set { _waitTime = value; } }

        TimeSpan _timeout = new TimeSpan(0, 5, 0); // default
        /// <summary>
        /// Set the timeout for pooled resources. The pooled object should inherit from AbstractTimedResource
        /// and implement the IDisposable.Dispose interface for the timeout event
        /// </summary>
        public TimeSpan Timeout { get { return _timeout; } set { _timeout = value; } }

        public AbstractPoolSource() 
        {
            this.MaxConsecutiveErrors = 8; // default to 8 errors
            this.WaitOnMaxConsecutiveErrors = new TimeSpan(0, 5, 0);
        }
    }

    public enum LoadingStrategy
    {
        Lazy = 0,
        Eager
    }
}

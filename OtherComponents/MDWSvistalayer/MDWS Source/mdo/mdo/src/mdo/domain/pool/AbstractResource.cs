using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.pool
{
    public abstract class AbstractResource //: IDisposable
    {
        /// <summary>
        /// When implementing an AbstractResourcePool, your pooled items should inherit this class 
        /// and implement this method (even if it always returns true) to ensure items being retrieved
        /// from the pool are still valid (e.g. a connection is still connected)
        /// </summary>
        /// <returns></returns>
        public abstract bool isAlive();

        public abstract void CleanUp(); // use in place of dispose because of ObjectDisposedException
       // public abstract void Dispose();
    }
}

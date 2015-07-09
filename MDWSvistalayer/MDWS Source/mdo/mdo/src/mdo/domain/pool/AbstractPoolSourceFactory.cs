using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.pool
{
    public abstract class AbstractPoolSourceFactory
    {
        public AbstractPoolSource Default { get; set; }

        /// <summary>
        /// Empty constructor defined to enforce a default AbstractPoolSource instantiation - see ConnectionPoolSourceFactory constructor for example
        /// </summary>
        public AbstractPoolSourceFactory() { }

        public AbstractPoolSourceFactory(AbstractPoolSource defaultSource)
        {
            this.Default = defaultSource;
        }

        /// <summary>
        /// Get the pool source for an implementation of a resource pool
        /// </summary>
        /// <param name="source">The pool's expected source type</param>
        /// <returns>The pool's abstractpoolsource implementation type</returns>
        public abstract AbstractPoolSource getPoolSource(object source);

        /// <summary>
        /// Get the pool source for an implementation of a collection of pools
        /// </summary>
        /// <param name="sources">The pool's expected source collection</param>
        /// <returns>The pool's type for collection of source</returns>
        public abstract object getPoolSources(object sources);
    }
}

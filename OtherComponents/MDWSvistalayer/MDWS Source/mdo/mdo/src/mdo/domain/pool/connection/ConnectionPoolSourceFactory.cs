using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.dao;

namespace gov.va.medora.mdo.domain.pool.connection
{
    public class ConnectionPoolSourceFactory : AbstractPoolSourceFactory
    {
        /// <summary>
        /// Use to set hard coded pool defaults
        /// </summary>
        public ConnectionPoolSourceFactory() : base()
        {
            this.Default = new ConnectionPoolSource()
            {
                LoadStrategy = LoadingStrategy.Lazy,
                MaxPoolSize = 8,
                MinPoolSize = 2,
                PoolExpansionSize = 2,
                WaitTime = new TimeSpan(0, 1, 0)
            };
        }

        /// <summary>
        /// Instantiate the factory with a specified default pool source
        /// </summary>
        /// <param name="defaultSource">The default source to use for calls to getPoolSource(s)</param>
        public ConnectionPoolSourceFactory(AbstractPoolSource defaultSource) : base(defaultSource) { }

        /// <summary>
        /// Get a ConnectionPoolSource given a DataSource using the factory's default pool source
        /// </summary>
        /// <param name="source">DataSource</param>
        /// <returns>ConnectionPoolSource</returns>
        public override AbstractPoolSource getPoolSource(object source)
        {
            if (!(source is DataSource))
            {
                throw new ArgumentException("Invalid source. Must supply a DataSource");
            }
            ConnectionPoolSource theSrc = new ConnectionPoolSource();
            theSrc.CxnSource = (DataSource)source;
            if (this.Default is ConnectionPoolSource && null != ((ConnectionPoolSource)this.Default).CxnSource) 
            {
                theSrc.CxnSource.Protocol = ((ConnectionPoolSource)this.Default).CxnSource.Protocol; // if we set the default pool source's protocol, we should copy it over
            }
            theSrc.Credentials = this.Default.Credentials;
            theSrc.LoadStrategy = this.Default.LoadStrategy;
            theSrc.MaxPoolSize = this.Default.MaxPoolSize;
            theSrc.MinPoolSize = this.Default.MinPoolSize;
            theSrc.PoolExpansionSize = this.Default.PoolExpansionSize;
            theSrc.WaitTime = this.Default.WaitTime;
            return theSrc;
        }

        /// <summary>
        /// Get a ConnectionsPoolsSource given a SiteTable using the factory's default pool source
        /// </summary>
        /// <param name="sources">SiteTable</param>
        /// <returns>ConnectionPoolsSource</returns>
        public override object getPoolSources(object sources)
        {
            if (!(sources is SiteTable))
            {
                throw new ArgumentException("Invalid source. Must supply a SiteTable");
            }
            SiteTable siteTable = (SiteTable)sources;
            Site[] sites = new Site[siteTable.Sites.Count];
            for (int i = 0; i < siteTable.Sites.Count; i++)
            {
                sites[i] = (Site)siteTable.Sites.GetByIndex(i);
            }
            ConnectionPoolsSource result = new ConnectionPoolsSource();
            result.CxnSources = new Dictionary<string, ConnectionPoolSource>();
            foreach (Site site in sites)
            {
                if (site.Sources == null || site.Sources.Length == 0)
                {
                    continue;
                }
                for (int i = 0; i < site.Sources.Length; i++)
                {
                    if (String.Equals(site.Sources[i].Protocol, "VISTA", StringComparison.CurrentCultureIgnoreCase)
                        || String.Equals(site.Sources[i].Protocol, "PVISTA", StringComparison.CurrentCultureIgnoreCase))
                    {
                        result.CxnSources.Add(site.Id, (ConnectionPoolSource)getPoolSource(site.Sources[i]));
                        break;
                    }
                }
            }
            return result;
        }
    }
}

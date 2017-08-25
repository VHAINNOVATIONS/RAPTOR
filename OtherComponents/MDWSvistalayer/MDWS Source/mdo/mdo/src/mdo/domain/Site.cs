using System;
using System.Collections.Generic;
using System.Collections;
using System.Text;

namespace gov.va.medora.mdo
{
    public class Site
    {
        string id;
        string name;
        string displayName;
        string moniker;
        string regionId;
        string lastEventTimestamp;
        string lastEventReason;
        string errMsg;
        DataSource[] dataSources;
        string parentSiteId;
        Site[] childSites;
        string address;
        string city;
        string state;
        string systemName;
        string siteType;

        public Site() {}

        public Site(string id, string name)
        {
            Id = id;
            Name = name;
        }

        public string Id
        {
            get
            {
                return id;
            }
            set
            {
                id = value;
            }
        }

        public string Name
        {
            get
            {
                return name;
            }
            set
            {
                name = value;
            }
        }

        public string DisplayName
        {
            get
            {
                return displayName;
            }
            set
            {
                displayName = value;
            }
        }

        public string Moniker
        {
            get
            {
                return moniker;
            }
            set
            {
                moniker = value;
            }
        }

        public string RegionId
        {
            get
            {
                return regionId;
            }
            set
            {
                regionId = value;
            }
        }

        public string LastEventTimestamp
        {
            get
            {
                return lastEventTimestamp;
            }
            set
            {
                lastEventTimestamp = value;
            }
        }

        public string LastEventReason
        {
            get
            {
                return lastEventReason;
            }
            set
            {
                lastEventReason = value;
            }
        }

        public string ErrMsg
        {
            get
            {
                return errMsg;
            }
            set
            {
                errMsg = value;
            }
        }

        public DataSource[] Sources
        {
            get
            {
                return dataSources;
            }
            set
            {
                dataSources = value;
            }
        }

        public DataSource getDataSourceByModality(string modality)
        {
            for (int i = 0; i < Sources.Length; i++)
            {
                if (String.Equals(Sources[i].Modality, modality, StringComparison.CurrentCultureIgnoreCase))
                {
                    return Sources[i];
                }
            }
            return null;
        }

        public string ParentSiteId
        {
            get { return parentSiteId; }
            set { parentSiteId = value; }
        }

        public Site[] ChildSites
        {
            get { return childSites; }
            set { childSites = value; }
        }

        public string Address
        {
            get { return address; }
            set { address = value; }
        }

        public string City
        {
            get { return city; }
            set { city = value; }
        }

        public string State
        {
            get { return state; }
            set { state = value; }
        }

        public string SystemName
        {
            get { return systemName; }
            set { systemName = value; }
        }

        public string SiteType
        {
            get { return siteType; }
            set { siteType = value; }
        }
    }
}

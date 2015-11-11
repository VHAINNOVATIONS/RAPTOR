using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class SiteId
    {
        string id;
        string name;
        string lastSeenDate;
        string lastEvent;

        public SiteId() 
        {
            Id = "";
            Name = "";
            LastSeenDate = "";
            LastEvent = "";
        }

        public SiteId(string id)
        {
            Id = id;
            Name = "";
            LastSeenDate = "";
            LastEvent = "";
        }

        public SiteId(string id, string name)
        {
            Id = id;
            Name = name;
            LastSeenDate = "";
            LastEvent = "";
        }

        public SiteId(string id, string name, string lastSeenDate)
        {
            Id = id;
            Name = name;
            LastSeenDate = lastSeenDate;
            LastEvent = "";
        }

        public SiteId(string id, string name, string lastSeenDate, string lastEvent)
        {
            Id = id;
            Name = name;
            LastSeenDate = lastSeenDate;
            LastEvent = lastEvent;
        }

        public string Id
        {
            get { return id; }
            set { id = value; }
        }

        public string Name
        {
            get { return name; }
            set { name = value; }
        }

        public string LastSeenDate
        {
            get { return lastSeenDate; }
            set { lastSeenDate = value; }
        }

        public string LastEvent
        {
            get { return lastEvent; }
            set { lastEvent = value; }
        }

        public static bool operator == (SiteId id1, SiteId id2)
        {
            if (object.ReferenceEquals(id1,null))
            {
                return object.ReferenceEquals(id2,null);
            }
            if (object.ReferenceEquals(id2, null))
            {
                return false;
            }
            if (id1.GetType() != id2.GetType())
            {
                return false;
            }
            if (id1.Id != id2.Id)
            {
                return false;
            }
            return id1.Name == id2.Name;
        }

        public static bool operator != (SiteId id1, SiteId id2)
        {
            return !(id1 == id2);
        }
    }
}

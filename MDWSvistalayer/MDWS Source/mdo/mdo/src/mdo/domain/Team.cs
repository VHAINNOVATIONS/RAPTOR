using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class Team
    {
        string id;
        string name;
        string pcpName;
        string attendingName;

        public Team() {}

        public Team(string id, string name)
        {
            Id = id;
            Name = name;
        }

        public Team(string id, string name, string pcpName, string attendingName)
        {
            Id = id;
            Name = name;
            PcpName = pcpName;
            AttendingName = attendingName;
        }

        public string Id
        {
            get {return id;}
            set {id = value;}
        }

        public string Name
        {
            get {return name;}
            set {name = value;}
        }

        public string PcpName
        {
            get { return pcpName; }
            set { pcpName = value; }
        }

        public string AttendingName
        {
            get { return attendingName; }
            set { attendingName = value; }
        }
    }
}

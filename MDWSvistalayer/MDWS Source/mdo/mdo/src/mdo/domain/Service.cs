using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class Service
    {
        string id;
        string name;
        string abbr;
        User chief;
        Service parent;
        string location;
        string mailSymbol;
        string type;

        public Service() { }

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

        public string Abbreviation
        {
            get { return abbr; }
            set { abbr = value; }
        }

        public User Chief
        {
            get { return chief; }
            set { chief = value; }
        }

        public Service ParentService
        {
            get { return parent; }
            set { parent = value; }
        }

        public string Location
        {
            get { return location; }
            set { location = value; }
        }

        public string MailSymbol
        {
            get { return mailSymbol; }
            set { mailSymbol = value; }
        }

        public string Type
        {
            get { return type; }
            set { type = value; }
        }
    }
}

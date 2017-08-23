using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class TreatingSpecialty
    {
        string id;
        string name;
        KeyValuePair<string, string> specialty;
        KeyValuePair<string, string> service;

        public TreatingSpecialty() { }

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

        public KeyValuePair<string, string> Specialty
        {
            get { return specialty; }
            set { specialty = value; }
        }

        public KeyValuePair<string, string> Service
        {
            get { return service; }
            set { service = value; }
        }
    }
}

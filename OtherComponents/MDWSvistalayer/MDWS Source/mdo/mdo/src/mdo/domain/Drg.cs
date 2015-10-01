using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class Drg
    {
        string id;
        string description;

        public Drg() { }

        public Drg(string id, string description)
        {
            Id = id;
            Description = description;
        }

        public string Id
        {
            get { return id; }
            set { id = value; }
        }

        public string Description
        {
            get { return description; }
            set { description = value; }
        }
    }
}

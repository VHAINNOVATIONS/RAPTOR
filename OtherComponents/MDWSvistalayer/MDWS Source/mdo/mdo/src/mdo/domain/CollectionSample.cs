using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class CollectionSample
    {
        string id;
        string name;
        KeyValuePair<string, string> defaultSpecimen;

        public CollectionSample() { }

        public CollectionSample(string id, string name, KeyValuePair<string, string> defaultSpecimen)
        {
            Id = id;
            Name = name;
            DefaultSpecimen = defaultSpecimen;
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

        public KeyValuePair<string, string> DefaultSpecimen
        {
            get { return defaultSpecimen; }
            set { defaultSpecimen = value; }
        }
    }
}

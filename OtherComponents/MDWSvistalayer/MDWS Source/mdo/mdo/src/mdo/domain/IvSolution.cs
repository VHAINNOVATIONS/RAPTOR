using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class IvSolution
    {
        string id;
        string name;
        KeyValuePair<string, string> pharmacyOrderableItem;

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

        public KeyValuePair<string, string> PharmacyOrderableItem
        {
            get { return pharmacyOrderableItem; }
            set { pharmacyOrderableItem = value; }
        }
    }
}

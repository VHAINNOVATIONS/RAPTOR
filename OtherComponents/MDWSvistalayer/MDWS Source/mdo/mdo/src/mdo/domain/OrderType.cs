using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class OrderType
    {
        string id;
        string name1;
        string name2;
        
        public bool RequiresApproval;

        public OrderType() { }

        public OrderType(string id, string name1, string name2)
        {
            Id = id;
            Name1 = name1;
            Name2 = name2;
        }

        public string Id
        {
            get { return id; }
            set { id = value; }
        }

        public string Name1
        {
            get { return name1; }
            set { name1 = value; }
        }


        public string Name2
        {
            get { return name2; }
            set { name2 = value; }
        }

    }
}

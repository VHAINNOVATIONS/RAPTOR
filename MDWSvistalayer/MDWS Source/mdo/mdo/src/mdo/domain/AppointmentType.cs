using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo
{
    public class AppointmentType
    {
        public string ID { get; set; }
        public string Name { get; set; }
        public bool Active { get; set; }
        public string Description { get; set; }
        public string Synonym { get; set; }

        public AppointmentType()
        {
            Active = true;
        }

        public AppointmentType(string id, string name)
        {
            Active = true;
            ID = id;
            Name = name;
        }
    }
}

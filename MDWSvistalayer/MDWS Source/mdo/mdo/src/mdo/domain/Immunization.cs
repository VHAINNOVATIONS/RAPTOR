using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo
{
    public class Immunization
    {
        public Immunization() { }

        public User Administrator { get; set; }
        public string AdministeredDate { get; set; }
        public string AnatomicSurface { get; set; }
        public string Comments { get; set; }
        public string Contraindicated { get; set; }
        public CptCode CptCode { get; set; }
        public Visit Encounter { get; set; }
        public string Id { get; set; }
        public string LotNumber { get; set; }
        public string Manufacturer { get; set; }
        public string Name { get; set; }
        public User OrderedBy { get; set; }
        public string Reaction { get; set; }
        public string Series { get; set; }
        public string ShortName { get; set; }
    }
}

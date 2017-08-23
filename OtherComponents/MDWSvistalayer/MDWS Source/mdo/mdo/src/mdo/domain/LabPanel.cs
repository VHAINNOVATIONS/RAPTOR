using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo
{
    public class LabPanel
    {
        public IList<LabTest> Tests { get; set; }

        public String Name { get; set; }

        public Order Order { get; set; }
    }
}

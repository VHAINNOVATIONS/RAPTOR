using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class LabTestArray : AbstractArrayTO
    {
        public LabTestTO[] tests;

        public LabTestArray() { }

        public LabTestArray(IList<LabTest> labTests)
        {
            if (labTests == null || labTests.Count == 0)
            {
                return;
            }

            this.count = labTests.Count;
            tests = new LabTestTO[this.count];
            for (int i = 0; i < labTests.Count; i++)
            {
                tests[i] = new LabTestTO(labTests[i]);
            }
        }
    }
}
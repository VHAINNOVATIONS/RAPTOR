using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedLabTestArray : AbstractTaggedArrayTO
    {
        public LabTestArray labTests;

        public TaggedLabTestArray() { }

        public TaggedLabTestArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedLabTestArray(string tag, IList<LabTest> tests)
        {
            this.tag = tag;
            setLabTests(tests);
        }

        public TaggedLabTestArray(string tag, LabTest[] tests)
        {
            this.tag = tag;
            setLabTests(tests);
        }

        public TaggedLabTestArray(string tag, LabTest test)
        {
            this.tag = tag;
            setLabTests(test);
        }


        void setLabTests(IList<LabTest> tests)
        {
            if (tests == null || tests.Count == 0)
            {
                return;
            }

            this.count = tests.Count;
            this.labTests = new LabTestArray(tests);
        }

        void setLabTests(LabTest[] tests)
        {
            if (tests == null || tests.Length == 0)
            {
                return;
            }

            IList<LabTest> temp = new List<LabTest>();
            foreach (LabTest tst in tests)
            {
                temp.Add(tst);
            }
            this.count = tests.Length;
            this.labTests = new LabTestArray(temp);
        }

        void setLabTests(LabTest test)
        {
            if (test == null)
            {
                return;
            }

            IList<LabTest> temp = new List<LabTest>() { test };
            this.count = 1;
            this.labTests = new LabTestArray(temp);
        }
    }
}
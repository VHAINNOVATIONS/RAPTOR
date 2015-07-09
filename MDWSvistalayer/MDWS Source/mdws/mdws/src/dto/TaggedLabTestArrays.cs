using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedLabTestArrays : AbstractArrayTO
    {
        public TaggedLabTestArray[] arrays;

        public TaggedLabTestArrays() { }

        public TaggedLabTestArrays(IndexedHashtable labTests)
        {
            if (labTests == null || labTests.Count == 0)
            {
                return;
            }

            arrays = new TaggedLabTestArray[labTests.Count];

            for (int i = 0; i < labTests.Count; i++)
            {
                string tag = (string)labTests.GetKey(i);

                if (labTests.GetValue(i) == null)
                {
                    arrays[i] = new TaggedLabTestArray(tag);
                }
                if (labTests.GetValue(i) is IList<LabTest>)
                {
                    arrays[i] = new TaggedLabTestArray(tag, (IList<LabTest>)labTests.GetValue(i));
                }
                else if (labTests.GetValue(i) is LabTest[])
                {
                    arrays[i] = new TaggedLabTestArray(tag);
                }
                else if (labTests.GetValue(i) is LabTest)
                {
                    arrays[i] = new TaggedLabTestArray(tag);
                }
                else
                {
                    return; 
                }
            }
        }
    }
}
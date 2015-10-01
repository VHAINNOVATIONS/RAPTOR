using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;
using System.Collections;

namespace gov.va.medora.mdws.dto
{
    [Serializable]
    public class TaggedLabReportArrays : AbstractArrayTO
    {
        public TaggedLabReportArray[] arrays;

        public TaggedLabReportArrays() { }

        public TaggedLabReportArrays(IndexedHashtable ihs)
        {
            if (ihs == null || ihs.Count == 0)
            {
                return;
            }

            this.count = ihs.Count;
            arrays = new TaggedLabReportArray[ihs.Count];

            for (int i = 0; i < ihs.Count; i++)
            {
                string tag = (string)ihs.GetKey(i);

                if (ihs.GetValue(i) == null)
                {
                    arrays[i] = new TaggedLabReportArray();
                }
                else if (MdwsUtils.isException(ihs.GetValue(i)))
                {
                    arrays[i] = new TaggedLabReportArray(tag, ihs.GetValue(i) as Exception);
                }
                else if (ihs.GetValue(i).GetType().Equals(typeof(Hashtable)))
                {
                    arrays[i] = new TaggedLabReportArray(tag, (IList<LabReport>)((Hashtable)ihs.GetValue(i))["accessions"]);
                }
            }
        }
    }
}
using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;
using System.Collections;

namespace gov.va.medora.mdws.dto
{
    [Serializable]
    public class TaggedImagingExamArrays : AbstractTaggedArrayTO
    {
        public TaggedImagingExamArray[] arrays;
        
        public TaggedImagingExamArrays() { }


        public TaggedImagingExamArrays(IndexedHashtable ihs)
        {
            if (ihs == null || ihs.Count <= 0)
            {
                return;
            }

            this.count = ihs.Count;
            arrays = new TaggedImagingExamArray[ihs.Count];

            for (int i = 0; i < ihs.Count; i++)
            {
                if (ihs.GetValue(i) == null)
                {
                    arrays[i] = new TaggedImagingExamArray((string)ihs.GetKey(i));
                }
                else if (MdwsUtils.isException(ihs.GetValue(i)))
                {
                    arrays[i] = new TaggedImagingExamArray((string)ihs.GetKey(i), ihs.GetValue(i) as Exception);
                }
                else if (ihs.GetValue(i).GetType() == typeof(Hashtable))
                {
                    arrays[i] = new TaggedImagingExamArray((string)ihs.GetKey(i), ((Hashtable)ihs.GetValue(i))["radiologyExams"] as IList<ImagingExam>);
                }
            }
        }
    }
}
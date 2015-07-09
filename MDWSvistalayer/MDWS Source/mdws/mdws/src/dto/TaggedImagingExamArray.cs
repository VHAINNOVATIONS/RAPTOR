using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    [Serializable]
    public class TaggedImagingExamArray : AbstractTaggedArrayTO
    {
        public ImagingExamTO[] imagingExams;

        public TaggedImagingExamArray() { } 

        public TaggedImagingExamArray(string tag)
        {
            this.count = 0;
            this.tag = tag;
        }

        public TaggedImagingExamArray(string tag, Exception exception)
        {
            this.tag = tag;
            this.fault = new FaultTO(exception);
        }

        public TaggedImagingExamArray(string tag, IList<ImagingExam> mdos)
        {
            this.tag = tag;

            if (mdos == null || mdos.Count == 0)
            {
                return;
            }

            this.count = mdos.Count;
            imagingExams = new ImagingExamTO[mdos.Count];

            for (int i = 0; i < mdos.Count; i++)
            {
                imagingExams[i] = new ImagingExamTO(mdos[i]);
            }
        }
    }
}
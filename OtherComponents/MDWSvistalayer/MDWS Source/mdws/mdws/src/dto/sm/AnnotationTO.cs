using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace gov.va.medora.mdws.dto.sm
{
    [Serializable]
    public class AnnotationTO : BaseSmTO
    {
        //public ThreadTO Thread { get; set; }
        public string threadAnnotation;
        /* author is most probably a Clinician
         * but I can't guarantee that there won't
         * be a need for an administrator to annotate
         * thread
         */
        public gov.va.medora.mdws.dto.sm.SmUserTO author;

        public AnnotationTO() { }

        public AnnotationTO(gov.va.medora.mdo.domain.sm.Annotation annotation)
        {
            if (annotation == null)
            {
                return;
            }

            id = annotation.Id;
            threadAnnotation = annotation.ThreadAnnotation;
            author = new SmUserTO(annotation.Author);
        }
    }
}
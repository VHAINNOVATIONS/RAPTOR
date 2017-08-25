using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm
{
    public class Annotation : BaseModel
    {
        public Thread Thread { get; set; }
        public string ThreadAnnotation { get; set; }
        /* author is most probably a Clinician
         * but I can't guarantee that there won't
         * be a need for an administrator to annotate
         * thread
         */
        public gov.va.medora.mdo.domain.sm.User Author { get; set; }
    }
}

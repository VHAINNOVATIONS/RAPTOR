using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class DiagnosisTO : AbstractTO
    {
        string icd9;
        string text;
        bool primary;

        public DiagnosisTO() { }

        public DiagnosisTO(Diagnosis mdo)
        {
            this.icd9 = mdo.Icd9;
            this.text = mdo.Text;
            this.primary = mdo.Primary;
        }
    }
}

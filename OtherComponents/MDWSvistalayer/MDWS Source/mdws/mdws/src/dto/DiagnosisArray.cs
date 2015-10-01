using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class DiagnosisArray : AbstractArrayTO
    {
        public DiagnosisTO[] dx;

        public DiagnosisArray() { }

        public DiagnosisArray(Diagnosis[] mdo)
        {
            if (mdo == null || mdo.Length == 0)
            {
                count = 0;
                return;
            }
            dx = new DiagnosisTO[mdo.Length];
            for (int i = 0; i < mdo.Length; i++)
            {
                dx[i] = new DiagnosisTO(mdo[i]);
            }
            count = mdo.Length;
        }
    }
}

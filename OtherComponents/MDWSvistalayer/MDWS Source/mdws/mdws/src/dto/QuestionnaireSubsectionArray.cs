using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class QuestionnaireSubsectionArray : AbstractArrayTO
    {
        public QuestionnaireSubsectionTO[] subsections;

        public QuestionnaireSubsectionArray() { }

        public QuestionnaireSubsectionArray(List<QuestionnaireSubsection> mdo)
        {
            if (mdo == null || mdo.Count == 0)
            {
                count = 0;
                return;
            }
            subsections = new QuestionnaireSubsectionTO[mdo.Count];
            for (int i = 0; i < mdo.Count; i++)
            {
                subsections[i] = new QuestionnaireSubsectionTO(mdo[i]);
            }
            count = mdo.Count;
        }
    }
}

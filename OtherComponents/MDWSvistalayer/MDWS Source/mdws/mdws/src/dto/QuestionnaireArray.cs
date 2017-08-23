using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class QuestionnaireArray : AbstractArrayTO
    {
        public QuestionnaireTO[] questionnaires;

        public QuestionnaireArray() { }

        public QuestionnaireArray(List<Questionnaire> mdo)
        {
            if (mdo == null || mdo.Count == 0)
            {
                count = 0;
                return;
            }
            questionnaires = new QuestionnaireTO[mdo.Count];
            for (int i = 0; i < mdo.Count; i++)
            {
                questionnaires[i] = new QuestionnaireTO(mdo[i]);
            }
            count = mdo.Count;
        }
    }
}

using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class QuestionnairePageArray : AbstractArrayTO
    {
        public QuestionnairePageTO[] pages;

        public QuestionnairePageArray() { }

        public QuestionnairePageArray(List<QuestionnairePage> mdo)
        {
            if (mdo == null || mdo.Count == 0)
            {
                count = 0;
                return;
            }
            pages = new QuestionnairePageTO[mdo.Count];
            for (int i = 0; i < mdo.Count; i++)
            {
                pages[i] = new QuestionnairePageTO(mdo[i]);
            }
            count = mdo.Count;
        }
    }
}

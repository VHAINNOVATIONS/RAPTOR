using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class QuestionnaireSectionArray : AbstractArrayTO
    {
        public QuestionnaireSectionTO[] sections;

        public QuestionnaireSectionArray() { }

        public QuestionnaireSectionArray(List<QuestionnaireSection> mdo)
        {
            if (mdo == null || mdo.Count == 0)
            {
                count = 0;
                return;
            }
            sections = new QuestionnaireSectionTO[mdo.Count];
            for (int i = 0; i < mdo.Count; i++)
            {
                sections[i] = new QuestionnaireSectionTO(mdo[i]);
            }
            count = mdo.Count;
        }
    }
}

using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class QuestionArray : AbstractArrayTO
    {
        public QuestionTO[] questions;

        public QuestionArray() { }

        public QuestionArray(List<QuestionnaireQuestion> mdo)
        {
            if (mdo == null || mdo.Count == 0)
            {
                count = 0;
                return;
            }
            questions = new QuestionTO[mdo.Count];
            for (int i = 0; i < mdo.Count; i++)
            {
                questions[i] = new QuestionTO(mdo[i]);
            }
            count = mdo.Count;
        }
    }
}

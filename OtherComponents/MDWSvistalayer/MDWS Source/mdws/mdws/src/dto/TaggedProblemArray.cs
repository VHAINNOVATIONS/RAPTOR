using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedProblemArray : AbstractTaggedArrayTO
    {
        public ProblemTO[] problems;

        public TaggedProblemArray() { }

        public TaggedProblemArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedProblemArray(String tag, IList<Problem> mdos)
        {
            Problem[] temp = null;
            if (mdos != null && mdos.Count > 0)
            {
                mdos.CopyTo(temp, 0);
            }
            setTagAndProblems(tag, temp);
        }

        public TaggedProblemArray(string tag, Problem[] mdos)
        {
            setTagAndProblems(tag, mdos);
        }

        public TaggedProblemArray(string tag, Problem mdo)
        {
            this.tag = tag;
            if (mdo == null)
            {
                this.count = 0;
                return;
            }
            this.problems = new ProblemTO[1];
            this.problems[0] = new ProblemTO(mdo);
            this.count = 1;
        }

        public TaggedProblemArray(string tag, Exception e)
        {
            this.tag = tag;
            this.fault = new FaultTO(e);
        }

        void setTagAndProblems(String tag, Problem[] mdos)
        {
            this.tag = tag;
            if (mdos == null)
            {
                this.count = 0;
                return;
            }
            this.problems = new ProblemTO[mdos.Length];
            for (int i = 0; i < mdos.Length; i++)
            {
                this.problems[i] = new ProblemTO(mdos[i]);
            }
            this.count = problems.Length;
        }
    }
}

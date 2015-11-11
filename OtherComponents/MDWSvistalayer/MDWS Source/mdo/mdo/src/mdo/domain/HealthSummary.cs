#define REFACTORING_2883 // #2883 health summaries
using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class HealthSummary : MdoDocument
    {
#if REFACTORING_2883
        string occurrenceLimit;
        string timeLimit;
        string header;
        string segment;
        string file;
        string ifn;
        string zerothNode;

        public HealthSummary() { }

        public string OccurrenceLimit
        {
            get { return occurrenceLimit; }
            set { occurrenceLimit = value; }
        }

        public string TimeLimit
        {
            get { return timeLimit; }
            set { timeLimit = value; }
        }

        public string Header
        {
            get { return header; }
            set { header = value; }
        }

        public string Segment
        {
            get { return segment; }
            set { segment = value; }
        }

        public string File
        {
            get { return file; }
            set { file = value; }
        }

        public string Ifn
        {
            get { return ifn; }
            set { ifn = value; }
        }

        public string ZerothNode
        {
            get { return zerothNode; }
            set { zerothNode = value; }
        }
#endif // REFACTORING_2883

    }
}

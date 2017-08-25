using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class SnoMedDescription
    {
        string id;
        string term;

        public SnoMedDescription(string id, string term)
        {
            Id = id;
            Term = term;
        }

        public SnoMedDescription() { }

        public string Id
        {
            get { return id; }
            set { id = value; }
        }

        public string Term
        {
            get { return term; }
            set { term = value; }
        }
    }
}

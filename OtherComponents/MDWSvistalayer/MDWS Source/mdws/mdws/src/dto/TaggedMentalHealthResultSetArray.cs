using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedMentalHealthResultSetArray : AbstractTaggedArrayTO
    {
        public MentalHealthInstrumentResultSetTO[] items;

        public TaggedMentalHealthResultSetArray() { }

        public TaggedMentalHealthResultSetArray(string tag) {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedMentalHealthResultSetArray(string tag, List<MentalHealthInstrumentResultSet> mdoItems) {
            this.tag = tag;
            if (mdoItems == null)
            {
                this.count = 0;
                return;
            }
            items = new MentalHealthInstrumentResultSetTO[mdoItems.Count];
            for (int i = 0; i < mdoItems.Count; i++)
            {
                items[i] = new MentalHealthInstrumentResultSetTO(mdoItems[i]);
            }
            this.count = items.Length;
        }
    }
}
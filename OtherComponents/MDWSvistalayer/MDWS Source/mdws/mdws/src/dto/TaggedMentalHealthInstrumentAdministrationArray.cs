using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedMentalHealthInstrumentAdministrationArray : AbstractTaggedArrayTO
    {
        public MentalHealthInstrumentAdministrationTO[] items;

        public TaggedMentalHealthInstrumentAdministrationArray() { }

        public TaggedMentalHealthInstrumentAdministrationArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedMentalHealthInstrumentAdministrationArray(string tag, MentalHealthInstrumentAdministration[] mdoItems)
        {
            this.tag = tag;
            if (mdoItems == null)
            {
                this.count = 0;
                return;
            }
            items = new MentalHealthInstrumentAdministrationTO[mdoItems.Length];
            for (int i = 0; i < mdoItems.Length; i++)
            {
                items[i] = new MentalHealthInstrumentAdministrationTO(mdoItems[i]);
            }
            count = items.Length;
        }

        public TaggedMentalHealthInstrumentAdministrationArray(string tag, List<MentalHealthInstrumentAdministration> mdoItems)
        {
            this.tag = tag;
            if (mdoItems == null)
            {
                this.count = 0;
                return;
            }
            items = new MentalHealthInstrumentAdministrationTO[mdoItems.Count];
            for (int i = 0; i < mdoItems.Count; i++)
            {
                items[i] = new MentalHealthInstrumentAdministrationTO(mdoItems[i]);
            }
            count = items.Length;
        }

        public TaggedMentalHealthInstrumentAdministrationArray(string tag, MentalHealthInstrumentAdministration administration)
        {
            this.tag = tag;
            if (administration == null)
            {
                this.count = 0;
                return;
            }
            this.items = new MentalHealthInstrumentAdministrationTO[1];
            this.items[0] = new MentalHealthInstrumentAdministrationTO(administration);
            this.count = 1;
        }
    }
}

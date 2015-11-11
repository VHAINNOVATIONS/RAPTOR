using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedAdtArray : AbstractTaggedArrayTO
    {
        public AdtTO[] items;

        public TaggedAdtArray() { }

        public TaggedAdtArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedAdtArray(string tag, Adt[] mdoItems)
        {
            this.tag = tag;
            if (mdoItems == null)
            {
                this.count = 0;
                return;
            }
            items = new AdtTO[mdoItems.Length];
            for (int i = 0; i < mdoItems.Length; i++)
            {
                items[i] = new AdtTO(mdoItems[i]);
            }
            count = items.Length;
        }

        public TaggedAdtArray(string tag, Adt adt)
        {
            this.tag = tag;
            if (adt == null)
            {
                this.count = 0;
                return;
            }
            this.items = new AdtTO[1];
            this.items[0] = new AdtTO(adt);
            this.count = 1;
        }
    }
}

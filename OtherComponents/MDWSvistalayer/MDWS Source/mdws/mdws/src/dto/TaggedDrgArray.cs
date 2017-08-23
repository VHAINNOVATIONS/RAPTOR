using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedDrgArray : AbstractTaggedArrayTO
    {
        public DrgTO[] items;

        public TaggedDrgArray() { }

        public TaggedDrgArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedDrgArray(string tag, Drg[] mdoItems)
        {
            this.tag = tag;
            if (mdoItems == null)
            {
                this.count = 0;
                return;
            }
            items = new DrgTO[mdoItems.Length];
            for (int i = 0; i < mdoItems.Length; i++)
            {
                items[i] = new DrgTO(mdoItems[i]);
            }
            count = items.Length;
        }

        public TaggedDrgArray(string tag, Drg drg)
        {
            this.tag = tag;
            if (drg == null)
            {
                this.count = 0;
                return;
            }
            this.items = new DrgTO[1];
            this.items[0] = new DrgTO(drg);
            this.count = 1;
        }
    }
}

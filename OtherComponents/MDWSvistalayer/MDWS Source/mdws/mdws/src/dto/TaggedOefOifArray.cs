using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedOefOifArray : AbstractTaggedArrayTO
    {
        public OefOifTO[] items;

        public TaggedOefOifArray() { }

        public TaggedOefOifArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedOefOifArray(string tag, OEF_OIF[] mdoItems)
        {
            this.tag = tag;
            if (mdoItems == null)
            {
                this.count = 0;
                return;
            }
            items = new OefOifTO[mdoItems.Length];
            for (int i = 0; i < mdoItems.Length; i++)
            {
                items[i] = new OefOifTO(mdoItems[i]);
            }
            count = items.Length;
        }

        public TaggedOefOifArray(string tag, OEF_OIF item)
        {
            this.tag = tag;
            if (item == null)
            {
                this.count = 0;
                return;
            }
            this.items = new OefOifTO[1];
            this.items[0] = new OefOifTO(item);
            this.count = 1;
        }
    }
}

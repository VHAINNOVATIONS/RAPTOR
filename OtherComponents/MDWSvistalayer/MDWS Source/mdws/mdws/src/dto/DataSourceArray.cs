using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class DataSourceArray : AbstractArrayTO
    {
        public DataSourceTO[] items;

        public DataSourceArray() { }

        public DataSourceArray(DataSource mdo)
        {
            if (mdo == null)
            {
                return;
            }
            items = new DataSourceTO[1];
            items[0] = new DataSourceTO(mdo);
            count = 1;
        }

        public DataSourceArray(DataSource[] mdoItems)
        {
            if (mdoItems == null)
            {
                return;
            }
            items = new DataSourceTO[mdoItems.Length];
            for (int i = 0; i < mdoItems.Length; i++)
            {
                items[i] = new DataSourceTO(mdoItems[i]);
            }
            count = items.Length;
        }

        public DataSourceArray(IndexedHashtable t)
        {
            if (t.Count == 0)
            {
                return;
            }
            items = new DataSourceTO[t.Count];
            for (int i = 0; i < t.Count; i++)
            {
                if (t.GetValue(i).GetType().IsAssignableFrom(typeof(Exception)))
                {
                    fault = new FaultTO((Exception)t.GetValue(i));
                }
                //else if (t.GetValue(i) == null)
                //{
                //    items[i] = new TaggedAdtArray((string)t.GetKey(i));
                //}
                else
                {
                    items[i] = new DataSourceTO((DataSource)t.GetValue(i));
                }
            }
            count = items.Length;
        }
    }
}

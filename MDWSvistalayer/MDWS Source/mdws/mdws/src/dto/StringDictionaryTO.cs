using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace gov.va.medora.mdws.dto
{
    [Serializable]
    public class StringDictionaryTO : AbstractTO
    {
        public String[] keys;
        public String[] values;

        public StringDictionaryTO() { }

        public StringDictionaryTO(Dictionary<String, String> dictionary)
        {
            if (dictionary == null)
            {
                return;
            }

            keys = dictionary.Keys.ToArray();
            values = dictionary.Values.ToArray();
        }
    }
}
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.exceptions;

namespace gov.va.medora.mdo
{
    public class DdrGetsEntryDictionary
    {
        private Dictionary<String, KeyValuePair<String, String>> _ieDict = new Dictionary<string,KeyValuePair<string,string>>();

        public DdrGetsEntryDictionary() { }

        public IList<String> getKeys()
        {
            return _ieDict.Keys.ToList();
        }

        public bool hasExternal(String key)
        {
            return !String.IsNullOrEmpty(_ieDict[key].Value);
        }

        public bool hasKey(String key)
        {
            return _ieDict.ContainsKey(key);
        }

        public String getInternal(String key)
        {
            return _ieDict[key].Key;
        }

        public String getExternal(String key)
        {
            return _ieDict[key].Value;
        }

        /// <summary>
        /// Return the external value at the specified index. Returns an empty string if the specified index does not exist
        /// </summary>
        /// <param name="key"></param>
        /// <returns></returns>
        public String safeGetExternal(String key)
        {
            if (this.hasKey(key))
            {
                return this.getExternal(key);
            }
            return "";
        }

        /// <summary>
        /// Return the internal value at the specified index. Returns an empty string if the specified index does not exist
        /// </summary>
        /// <param name="key"></param>
        /// <returns></returns>
        public String safeGetInternal(String key)
        {
            if (this.hasKey(key))
            {
                return this.getInternal(key);
            }
            return "";
        }

        public String safeGetValue(String key)
        {
            if (this.hasKey(key))
            {
                return this.getValue(key);
            }
            return "";
        }

        internal void add(String key, String internalValue, String externalValue)
        {
            _ieDict.Add(key, new KeyValuePair<string, string>(internalValue, externalValue));
        }

        /// <summary>
        /// Returns the value at the specified index. Returns the external value, if present. Otherwise internal.
        /// </summary>
        /// <param name="key"></param>
        /// <returns></returns>
        public String getValue(String key)
        {
            if (hasExternal(key))
            {
                return getExternal(key);
            }
            else
            {
                return getInternal(key);
            }
        }

        public static DdrGetsEntryDictionary parse(String[] ddrGetsEntryResults)
        {
            DdrGetsEntryDictionary result = new DdrGetsEntryDictionary();

            if (ddrGetsEntryResults == null || ddrGetsEntryResults.Length <= 0)
            {
                return result;
            }

            // check for error
            if (ddrGetsEntryResults.Length > 0)
            {
                if (String.Equals(ddrGetsEntryResults[0], "[ERROR]", StringComparison.CurrentCultureIgnoreCase))
                {
                    if (ddrGetsEntryResults.Length > 1)
                    {
                        throw new MdoException(ddrGetsEntryResults[1]);
                    }
                    else
                    {
                        throw new MdoException("Unspecified DDR GETS ENTRY error");
                    }
                }
            }
            // end error

            for (int i = 0; i < ddrGetsEntryResults.Length; i++)
            {
                String[] currentLinePieces = ddrGetsEntryResults[i].Split(new char[] { '^' });

                if (currentLinePieces.Length < 4) // meta data column
                {
                    continue;
                }

                // just need to do one time
                if (!result.hasKey("IEN"))
                {
                    result.add("IEN", currentLinePieces[1], "");
                }

                String currentFieldNo = currentLinePieces[2];
                if (String.Equals("[WORD PROCESSING]", currentLinePieces[3], StringComparison.CurrentCultureIgnoreCase))
                {
                    StringBuilder sb = new StringBuilder();
                    while (!String.Equals(ddrGetsEntryResults[++i], "$$END$$", StringComparison.CurrentCultureIgnoreCase))
                    {
                        sb.AppendLine(ddrGetsEntryResults[i]);
                    }
                    result.add(currentFieldNo, sb.ToString(), "");
                }
                else
                {
                    result.add(currentFieldNo, currentLinePieces[3], currentLinePieces[4]);
                }
            }

            return result;
        }
    }
}

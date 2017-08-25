using System;
using System.Collections;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class IndexedHashtable
    {
        ArrayList keyArray;
        Hashtable hashTable;

        public IndexedHashtable() 
        {
            keyArray = new ArrayList();
            hashTable = new Hashtable();
        }

        public IndexedHashtable(int capacity)
        {
            keyArray = new ArrayList(capacity);
            hashTable = new Hashtable(capacity);
        }

        public void Add(Object key, Object value)
        {
            keyArray.Add(key);
            hashTable.Add(key, value);
        }

        public void Remove(Object key)
        {
            if (!hashTable.ContainsKey(key))
            {
                throw new KeyNotFoundException();
            }
            keyArray.Remove(key);
            hashTable.Remove(key);
        }

        public void Clear()
        {
            hashTable = new Hashtable();
            keyArray = new ArrayList();
        }

        public Object GetValue(String key)
        {
            return hashTable[key];
        }

        public Object GetValue(int index)
        {
            return hashTable[keyArray[index]];
        }

        public int Count
        {
            get { return keyArray.Count; }
        }

        public Object GetKey(int index)
        {
            return keyArray[index];
        }

        public bool ContainsKey(String target)
        {
            return hashTable.ContainsKey(target);
        }

    }
}

using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;
using gov.va.medora.mdo.dao;

namespace gov.va.medora.mdws.dto
{
    public class UserSecurityKeyArray : AbstractArrayTO
    {
        public UserSecurityKeyTO[] keys;

        public UserSecurityKeyArray() { }

        public UserSecurityKeyArray(UserSecurityKey[] mdo)
        {
            if (mdo == null)
            {
                return;
            }
            keys = new UserSecurityKeyTO[mdo.Length];
            for (int i = 0; i < mdo.Length; i++)
            {
                keys[i] = new UserSecurityKeyTO(mdo[i]);
            }
            count = mdo.Length;
        }

        public UserSecurityKeyArray(Dictionary<string, AbstractPermission> mdo)
        {
            if (mdo == null)
            {
                return;
            }
            keys = new UserSecurityKeyTO[mdo.Count];
            int i = 0;
            foreach (KeyValuePair<string, AbstractPermission> kvp in mdo)
            {
                keys[i++] = new UserSecurityKeyTO(kvp.Value);
            }
            count = mdo.Count;
        }
    }
}

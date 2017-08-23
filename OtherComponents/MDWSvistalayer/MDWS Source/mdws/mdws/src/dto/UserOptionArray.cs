using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class UserOptionArray : AbstractArrayTO
    {
        public UserOptionTO[] options;

        public UserOptionArray() { }

        public UserOptionArray(UserOption[] mdo)
        {
            if (mdo == null)
            {
                return;
            }
            options = new UserOptionTO[mdo.Length];
            for (int i = 0; i < mdo.Length; i++)
            {
                options[i] = new UserOptionTO(mdo[i]);
            }
            count = mdo.Length;
        }
    }
}

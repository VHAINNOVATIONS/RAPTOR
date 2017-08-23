using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class UserArray : AbstractArrayTO
    {
        public UserTO[] users;

        public UserArray() { }

        public UserArray(User[] mdoUsers)
        {
            if (mdoUsers == null)
            {
                return;
            }

            buildArray(mdoUsers);
        }

        public UserArray(IList<User> mdo)
        {
            if (mdo == null || mdo.Count == 0)
            {
                return;
            }
            User[] userAry = new User[mdo.Count];
            mdo.CopyTo(userAry, 0);
            buildArray(userAry);
        }

        void buildArray(User[] mdo)
        {
            users = new UserTO[mdo.Length];
            for (int i = 0; i < mdo.Length; i++)
            {
                users[i] = new UserTO(mdo[i]);
            }
            count = mdo.Length;
        }
    }
}

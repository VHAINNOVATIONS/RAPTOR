using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedUserArray : AbstractTaggedArrayTO
    {
        public UserTO[] users;

        public TaggedUserArray() { }

        public TaggedUserArray(string tag, User[] mdoUsers)
        {
            this.tag = tag;
            if (mdoUsers == null)
            {
                this.count = 0;
                return;
            }
            users = new UserTO[mdoUsers.Length];
            for (int i = 0; i < mdoUsers.Length; i++)
            {
                users[i] = new UserTO(mdoUsers[i]);
            }
            count = mdoUsers.Length;
        }

        public TaggedUserArray(string tag, User user)
        {
            this.tag = tag;
            if (user == null)
            {
                this.count = 0;
                return;
            }
            this.users = new UserTO[1];
            this.users[0] = new UserTO(user);
            this.count = 1;
        }

        public TaggedUserArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }
    }
}

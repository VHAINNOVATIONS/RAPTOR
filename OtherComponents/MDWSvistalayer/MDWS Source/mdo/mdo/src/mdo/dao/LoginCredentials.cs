using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao
{
    public class LoginCredentials : AbstractCredentials
    {
        User user;

        public LoginCredentials(User user) : base(user) {}

        public override CredentialType Type
        {
            get { return CredentialType.Login; }
        }
    }
}

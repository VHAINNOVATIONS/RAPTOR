using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm
{
    [Serializable]
    public class Signature : BaseModel
    {
        private string _name;

        protected string Name
        {
            get { return _name; }
            set { _name = value; }
        }
        private gov.va.medora.mdo.domain.sm.User _user;

        protected gov.va.medora.mdo.domain.sm.User User
        {
            get { return _user; }
            set { _user = value; }
        }
        private string _title;

        protected string Title
        {
            get { return _title; }
            set { _title = value; }
        }
    }
}

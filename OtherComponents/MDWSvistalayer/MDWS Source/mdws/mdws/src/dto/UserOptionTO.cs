using System;
using System.Data;
using System.Configuration;
using gov.va.medora.mdo;
using gov.va.medora.mdo.dao.vista;

namespace gov.va.medora.mdws.dto
{
    public class UserOptionTO : AbstractTO
    {
        public string number;
        public string id;
        public string name;
        public string displayName;
        public string key;
        public string reverseKey;
        public string type;
        public bool primaryOption;

        public UserOptionTO() { }

        public UserOptionTO(UserOption mdo)
        {
            this.number = mdo.Number;
            this.id = mdo.Id;
            this.name = mdo.Name;
            this.displayName = mdo.DisplayName;
            this.key = mdo.Key;
            this.reverseKey = mdo.ReverseKey;
            this.type = mdo.Type;
            this.primaryOption = mdo.PrimaryOption;
        }
    }
}

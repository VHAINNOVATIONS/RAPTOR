using System;
using System.Collections.Generic;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class ProstheticClaimTO : ClaimTO
    {
        public string prostheticId;
        public string prostheticName;

        public ProstheticClaimTO() { }

        public ProstheticClaimTO(ProstheticClaim mdo) : base(mdo)
        {
            this.prostheticId = mdo.ItemId;
            this.prostheticName = mdo.ItemName;
        }
    }
}
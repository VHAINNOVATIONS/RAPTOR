using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedOrderArray : AbstractTaggedArrayTO
    {
        public OrderTO[] items;

        public TaggedOrderArray()
        {
            /* Empty Constructor */
        }

        public TaggedOrderArray(string tag, Order[] orderTOs)
        {
            this.tag = tag;
            this.count = 0;

            if (orderTOs == null || orderTOs.Length == 0)
            {
                return;
            }

            items = new OrderTO[orderTOs.Length];
            this.count = orderTOs.Length;

            for (int i = 0; i < orderTOs.Length; i++)
            {
                items[i] = new OrderTO(orderTOs[i]);
            }
        }
    }
}
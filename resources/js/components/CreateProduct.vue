<template>
  <section>
    <div class="row">
      <div class="col-md-6">
        <div class="card shadow mb-4">
          <div class="card-body">
            <div class="form-group">
              <label for="">Product Name</label>
              <input
                type="text"
                v-model="product_name"
                placeholder="Product Name"
                class="form-control"
              />
            </div>
            <div class="form-group">
              <label for="">Product SKU</label>
              <input
                type="text"
                v-model="product_sku"
                placeholder="Product Name"
                class="form-control"
              />
            </div>
            <div class="form-group">
              <label for="">Description</label>
              <textarea
                v-model="description"
                id=""
                cols="30"
                rows="4"
                class="form-control"
              ></textarea>
            </div>
          </div>
        </div>

        <div class="card shadow mb-4">
          <div
            class="card-header py-3 d-flex flex-row align-items-center justify-content-between"
          >
            <h6 class="m-0 font-weight-bold text-primary">Media</h6>
          </div>
          <div class="card-body border">
            <vue-dropzone
              ref="myVueDropzone"
              id="dropzone"
              :options="dropzoneOptions"
              
              @vdropzone-success="afterUploadSuccess"
              v-on:vdropzone-files-added="filesAdded"
            ></vue-dropzone>
          </div>
        </div>
        <div class="card shadow mb-4" v-if="editableImages.length > 0">
          <div class="card-body border">
            <div class="row">
              <div style="position:relative;margin-bottom:15px" class="col-md-3" v-for="(image,index) in editableImages">
                <img :src="`http://127.0.0.1:8000/uploads/`+image.file_path" width="100%" height="100px"/>
                <button style="position:absolute;top:-10px;right:0px" class="btn btn-danger btn-sm" @click="removeImage(index,image.id,image.file_path)">X</button>
              </div>
            </div>
          </div>
        </div>
      </div>













      <div class="col-md-6">
        <div class="card shadow mb-4">
          <div
            class="card-header py-3 d-flex flex-row align-items-center justify-content-between"
          >
            <h6 class="m-0 font-weight-bold text-primary">Variants</h6>
          </div>
          <div class="card-body">
            <div class="row" v-for="(item, index) in product_variant">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="">Option</label>
                  <select v-model="item.option" class="form-control">
                    <option
                      v-on:blur="handleBlur"
                      v-for="variant in variants"
                      :value="variant.id"
                    >
                      {{ variant.title }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="col-md-8">
                <div class="form-group">
                  <label
                    v-if="product_variant.length != 1"
                    @click="
                      product_variant.splice(index, 1);
                      checkVariant;
                    "
                    class="float-right text-primary"
                    style="cursor: pointer"
                    >Remove</label
                  >
                  <label v-else for="">.</label>
                  <input-tag
                    v-model="item.tags"
                    @input="checkVariant"
                    class="form-control"
                  ></input-tag>
                </div>
              </div>
            </div>
          </div>
          <div
            class="card-footer"
            v-if="
              product_variant.length < variants.length &&
              product_variant.length < 3
            "
          >
            <button @click="newVariant" class="btn btn-primary">
              Add another option
            </button>
          </div>

          <div class="card-header text-uppercase">Preview</div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <td>Variant</td>
                    <td>Price</td>
                    <td>Stock</td>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="variant_price in product_variant_prices">
                    <td
                      v-if="
                        (variant_price.product_variant_one != null) |
                          (variant_price.product_variant_two != null) |
                          (variant_price.product_variant_three != null)
                      "
                    >
                      <span v-if="variant_price.product_variant_one != null"
                        >{{ variant_price.product_variant_one.variant }}/</span
                      ><span v-if="variant_price.product_variant_two != null"
                        >{{ variant_price.product_variant_two.variant }}/</span
                      ><span v-if="variant_price.product_variant_three != null"
                        >{{
                          variant_price.product_variant_three.variant
                        }}/</span
                      >
                    </td>
                    <td v-else>{{ variant_price.title }}</td>

                    <td>
                      <input
                        type="text"
                        class="form-control"
                        v-model="variant_price.price"
                      />
                    </td>
                    <td>
                      <input
                        type="text"
                        class="form-control"
                        v-model="variant_price.stock"
                      />
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <button
      v-if="this.product_id"
      @click="updateProduct"
      type="submit"
      class="btn btn-lg btn-primary"
    >
      Update
    </button>
    <button
      v-else
      @click="saveProduct"
      type="submit"
      class="btn btn-lg btn-primary"
    >
      Save
    </button>
    <button type="button" class="btn btn-secondary btn-lg">Cancel</button>
  </section>
</template>

<script>
import vue2Dropzone from "vue2-dropzone";
import "vue2-dropzone/dist/vue2Dropzone.min.css";
import InputTag from "vue-input-tag";

//var tempImg = [];

export default {
  components: {
    vueDropzone: vue2Dropzone,
    InputTag,
  },
  props: {
    variants: {
      type: Array,
      required: true,
    },
    products: {},
    product_id: {},
  },
  data() {
    return {
      product_name: "",
      product_sku: "",
      description: "",
      images: [],
      editableImages:[],
      product_variant: [
        {
          option: this.variants[0].id,
          tags: [],
        },
      ],
      product_variant_prices: [],
      dropzoneOptions: {
        url: "http://127.0.0.1:8000/image-upload",
        thumbnailWidth: 150,
        maxFilesize: 0.5,
        headers: { "My-Awesome-Header": "header value" },
        addRemoveLinks: true,
        //autoProcessQueue: false,
        //uploadMultiple:true
      },
    };
  },
  created() {
    if (this.product_id) {
      console.log(this.product_id);
      axios
        .get(`/product-edit/${this.product_id}`)
        .then((response) => {
          console.log(response.data);
          this.product_name = response.data.product.title;
          this.product_sku = response.data.product.sku;
          this.description = response.data.product.description;
          this.editableImages = response.data.product.product_image;

          //console.log(this.editableImages);

          // response.data.product.product_variant.forEach(element => {
          //     var self = this;
          //     self.product_variant.tags.push(element.variant);
          // });

          this.product_variant_prices =
            response.data.product.product_variant_price;
          // console.log(this.product_variant_pricesr);
        })
        .catch((error) => {
          console.log(error);
        });
    } else {
      console.log("it is create page");
    }
  },
  methods: {
    // it will push a new object into product variant
    newVariant() {
      let all_variants = this.variants.map((el) => el.id);
      let selected_variants = this.product_variant.map((el) => el.option);
      let available_variants = all_variants.filter(
        (entry1) => !selected_variants.some((entry2) => entry1 == entry2)
      );
      // console.log(available_variants)

      this.product_variant.push({
        option: available_variants[0],
        tags: [],
      });
    },

    handleBlur(val) {
      product_variant.tags = val;
    },

    // check the variant and render all the combination
    checkVariant() {
      let tags = [];
      this.product_variant_prices = [];
      this.product_variant.filter((item) => {
        tags.push(item.tags);
      });

      this.getCombn(tags).forEach((item) => {
        this.product_variant_prices.push({
          title: item,
          price: 0,
          stock: 0,
        });
      });
    },

    // combination algorithm
    getCombn(arr, pre) {
      console.log("executing: ");
      console.log(arr);
      pre = pre || "";
      if (!arr.length) {
        return pre;
      }
      let self = this;
      let ans = arr[0].reduce(function (ans, value) {
        return ans.concat(self.getCombn(arr.slice(1), pre + value + "/"));
      }, []);
      return ans;
    },

    // image processing
    filesAdded(files) {
      //this.images.push(files);
      console.log(this.images);
    },

    afterUploadSuccess(file,response){
      //tempImg.push(response);
      this.images.push(response);
      
      
    },
    // store product into database
    saveProduct() {

      this.$refs.myVueDropzone.processQueue();
      //console.log(tempImg)

      let product = {
          title: this.product_name,
          sku: this.product_sku,
          description: this.description,
          product_image: this.images,
          //product_image: tempImg,
          product_variant: this.product_variant,
          product_variant_prices: this.product_variant_prices
      }
      console.log(product);
      axios.post('/product', product).then(response => {
          if(response.data.status == 'created'){
              window.location.href = '/product'
          }
      }).catch(error => {
          console.log(error);
      })
    },

    updateProduct() {
      let product = {
        id: this.product_id,
        title: this.product_name,
        sku: this.product_sku,
        description: this.description,
        product_image: this.images,
        //product_image: tempImg,
        //product_variant: this.product_variant,
        product_variant_prices: this.product_variant_prices,
      };
      axios
        .post("/product-update", product)
        .then((response) => {
          if (response.data.status == "updated") {
            window.location.href = "/product";
          }
        })
        .catch((error) => {
          console.log(error);
        });
    },
    removeImage(index,id,img){
      axios.get(`/remove-product-image?id=${id}&img=${img}`)
      .then(res=>{
        console.log(res);
        if(res.data.message == 'deleted'){
          this.editableImages.splice(index, 1);
        }
        
      })
      
    },
  },

  
  mounted() {
    console.log("Component mounted.");
  },
};
</script>
